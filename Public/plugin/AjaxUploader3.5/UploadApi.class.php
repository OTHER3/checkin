<?php
/**
 * 文件上传接口
 * @author 随..心 <460415584@qq.com>
 */

/*
1、接收上传数据：文件名、文件大小、分片总数、分片大小、当前分片、唯一名称、
2、判断错误号、类型、大小
3、移动临时文件分片到tmp文件夹
4、合并文件分片
5、参数规定：
	唯一名称：md5(当前时间戳)
	返回数据格式：
	$data = array(
		code  => [1000:上传成功, 2000:合并成功]
		info  => '提示/错误信息',
		index => '当前分片',
		total => '分片总数',
	);
	path
	filename
	
	提交的数据格式：($_POST)
	Array
	(
	    [name] => ubuntu-16.04-desktop-amd64.iso
	    [uniName] => 1471328304179
	    [action] => upload
	    [total] => 709
	    [index] => 1
	)
	提交的数据格式：($_FILES)
	Array
	(
	    [file] => Array
	        (
	            [name] => blob
	            [type] => application/octet-stream
	            [tmp_name] => C:\tmp\php3C11.tmp
	            [error] => 0
	            [size] => 2097152
	        )
	)
状态码：
1000：上传成功
1001：文件类型不正确
1002：文件大小超过限制
1005：临时文件不存在
1006：移动临时文件失败

2000：合并成功
2001：当前分片文件不存在，无法进行合并操作
2002：文件夹没有写入权限，不允许进行合并操作
2003：文件不存在，无法进行合并操作
2004：文件锁定失败
2005：删除分片文件出错
2006：分片合并出错

注意事项：

上传单个文件超过8M，需要修改php.ini对应的配置：
file_uploads = on ;			是否允许通过HTTP上传文件的开关。默认为ON即是开
upload_max_filesize = 2m ;	允许上传文件大小的最大值。默认为2M
max_execution_time = 600 ;	每个PHP页面运行的最大时间值(秒)，默认30秒
max_input_time = 600 ;		每个PHP页面接收数据所需的最大时间，默认60秒
memory_limit = 8m ;			每个PHP页面所吃掉的最大内存，默认8M
post_max_size = 30M	;		其默认为8M
*/

class UploadApi
{
	public $name;	//原文件名
	public $uniName;//唯一文件名
	public $total;	//文件切割分片总数
	public $index;	//当前分片文件索引
	public $ext;	//文件扩展名
	public function __construct()
	{
		//拼接好上传路径后进行替换/符号
		$this->path = str_replace('\\', '/',dirname(__FILE__).'/Uploads/');
		$this->file = $_FILES;
		$this->name = $_POST['name'];
		$this->uniName = $_POST['uniName'];
		$this->index= $_POST['index'];
		$this->total= $_POST['total'];
		$this->ext  = $this->getExt();
	}

	public function uploadFile()
	{
		$data = array();
		$data['index'] = $this->index;
		$data['total'] = $this->total;
		//判断临时文件是否存在
		if(!is_file($this->file['file']['tmp_name'])){
			$data['code'] = 1005;
			$data['info'] = '临时文件不存在';
			return json_encode($data, 256);
		}

		//文件夹不存在则创建
		if(!is_dir($this->path)) mkdir($this->path,0777);

		//移动临时文件
		$move = move_uploaded_file($this->file['file']['tmp_name'], $this->path.$this->uniName.'_'.$this->index);
		if(!$move){
			$data['code'] = 1006;
			$data['info'] = '移动临时文件失败';
		}else{
			$data['code'] = 1000;
			$data['info'] = '上传成功';
		}
		return json_encode($data, 256);
	}

	//合并文件
	public function mergeFile()
	{
		$data = array();
		$data['index'] = $this->index;
		$data['total'] = $this->total;

		//判断当前分片文件是否存在
		if(!is_file($this->path.$this->uniName.'_'.$this->index)){
			$data['code'] = 2001;
			$data['info'] = '当前分片文件不存在，无法进行合并操作';
			return json_encode($data, 256);
		}
		
		//以只读的方式打开临时文件；'rb'参数的r是只读，b是防止读取出现怪异情况
		$cacheHandle = fopen($this->path.$this->uniName.'_'.$this->index, 'rb');
		//读取文件内容
		$contents = fread($cacheHandle, filesize($this->path.$this->uniName.'_'.$this->index));
		//销毁临时文件资源句柄
		$cache = fclose($cacheHandle);
		//清除filesize()获取文件大小的缓存
		clearstatcache();
		
		/*读取完成后进行写入操作*/
		
		//判断对文件夹是否有写入权限(验证上面添加权限是否有效)
		if(!is_writable($this->path)){
			$data['code'] = 2002;
			$data['info'] = '文件夹没有写入权限，不允许进行合并操作';
			return json_encode($data, 256);
		}

		//$index等于0的时文件还不存在无法进行判断，所以要跳过
		if( $this->index != 0 && !is_file($this->path.$this->uniName.'_'.$this->ext) ){
			$data['code'] = 2003;
			$data['info'] = '文件不存在，无法进行合并操作';
			return json_encode($data, 256);
		}
		
		//打开文件，不存在则创建文件，a+是以读写方式打开，指针移动到文件最后，b是防止出现怪异情况
		$handle = fopen($this->path.$this->uniName.'_'.$this->ext,'a+b');
		//对正在写入的文件进行锁定
		if(!flock($handle,LOCK_EX)){
			$data['code'] = 2004;
			$data['info'] = '文件锁定失败';
			return json_encode($data, 256);
		}

		//将读取到的内容写入目标文件,fwrite执行成功则返回写入的字节数。失败则返回 false。
		$fwrite = fwrite($handle,$contents);
		
		//销毁主文件资源句柄，锁定会自动解除
		$fclose = fclose($handle);
		
		//写入成功后销毁变量，释放内存
		unset($contents);
		
		if(!$this->delTempFile()){
			$data['code'] = 2005;
			$data['info'] = '删除分片文件出错';
			return json_encode($data, 256);
		}
		
		//写入并关闭成功后返回
		if( !$fwrite || !$fclose ){
			$data['code'] = 2006;
			$data['info'] = '分片合并出错';
			return json_encode($data, 256);
		}else{
			$data['code'] = 2000;
			$data['info'] = '合并成功';
			return json_encode($data, 256);
		}
	}
	
	//删除临时文件
	public function delTempFile()
	{
		if(!is_file($this->path.$this->uniName.'_'.$this->index)){
			return false;
		}
		unlink($this->path.$this->uniName.'_'.$this->index);
		return true;
	}

	/**
	 * 获取文件名
	 */
	public function getExt()
	{
		$array = explode('.', $this->name);
		$ext   = end($array);
		return $ext;
	}

}
