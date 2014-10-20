<?php

class mysql
{
	var $connid;
	var $querynum = 0;
	var $iscache = 1;
	var $caching = 0;
	var $cachedir = '';
	var $expires = 3600;
	var $isclient = 0;
	var $cursor = 0;
	var $result = array();
	var $cache_id = '';
	var $cache_file = '';
	var $dbname = '';
	var $search = array('/union(\s*(\/\*.*\*\/)?\s*)+select/i', '/load_file(\s*(\/\*.*\*\/)?\s*)+\(/i', '/into(\s*(\/\*.*\*\/)?\s*)+outfile/i');
	var $replace = array('union &nbsp; select', 'load_file &nbsp; (', 'into &nbsp; outfile');
	public $querysql = array();
	public $debug;
	
	static private $the_instance = null;

	/**
	 *
	 * @return mysql
	 * @access public static
	 */
	public static function instance() {
		return self::$the_instance === null ? self::$the_instance = new mysql() : self::$the_instance;
	}

	public function __construct(){
		$this->debug = (defined('NOMSG') && constant('NOMSG') == false)?false:true;
	}

	function connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect = 0){
		if($pconnect){
			if(!$this->connid = @mysql_pconnect($dbhost, $dbuser, $dbpw)){
				$this->halt('Can not connect to MySQL server');
			}
		}else{
			if(!$this->connid = @mysql_connect($dbhost, $dbuser, $dbpw, true)){
				$this->halt('Can not connect to MySQL server');
			}
		}
		if($this->version() > '4.1' && DB_CHARSET){
			mysql_query("SET NAMES '".DB_CHARSET."'" , $this->connid);
		}
		if($this->version() > '5.0'){
			mysql_query("SET sql_mode=''" , $this->connid);
		}
		if($dbname){
			if(!@mysql_select_db($dbname , $this->connid)){
				$this->halt('Cannot use database '.$dbname);
			}
			$this->dbname = $dbname;
		}
		return $this->connid;
	}

	function select_db($dbname){
		return mysql_select_db($dbname , $this->connid);
	}

	function query($sql , $type = '' , $expires = 3600, $dbname = ''){

		if(!$this->connid){
			$this->connect(DB_HOST, DB_USER, DB_PW, DB_NAME, DB_PCONNECT, DB_CHARSET);
		}
		if($this->isclient){
			$dbname = $dbname ? $dbname : $this->dbname;
			$this->select_db($dbname);
		}
		if($this->iscache && $type == 'CACHE' && stristr($sql, 'SELECT')){
			$this->caching = 1;
			$this->expires = $expires;
			return $this->_query_cache($sql);
		}
		//
		$_time = explode(' ', microtime());
		$sql_starttime = $_time[1] + $_time[0];
		//
		$this->caching = 0;
		$func = $type == 'UNBUFFERED' ? 'mysql_unbuffered_query' : 'mysql_query';
		if(!($query = $func($sql , $this->connid)) && $type != 'SILENT'){
			$this->halt('MySQL Query Error', $sql);
		}
		if ($this->debug){
			$_time = explode(' ', microtime());
			$sql_endtime = $_time[1] + $_time[0];
			$sql_costtime = $sql_endtime-$sql_starttime;
			if ($sql_costtime > 0.01){
				$this->querysql[] = ''.sprintf('%.4f',$sql_costtime).''.':'.$sql;
			}else {
				$this->querysql[] = sprintf('%.4f',$sql_costtime).':'.$sql;
			}
		}

		$this->querynum++;
		return $query;
	}

	function get_one($sql, $type = '', $expires = 3600, $dbname = ''){
		$query = $this->query($sql, $type, $expires, $dbname);
		$rs = $this->fetch_object($query);
		$this->free_result($query);
		return $rs;
	}

	function get_results($sql){
		$query = $this->query($sql, $type, $expires, $dbname);
		while ($row = $this->fetch_array($query,MYSQL_NUM)){
			$rt[] = $row[0];
		}
		$this->free_result($query);
		return (array)$rt;
	}

	function get_all($sql, $type = '', $expires = 3600, $dbname = ''){
		$rt = $rrt = $rto = array();
		$query = $this->query($sql, $type, $expires, $dbname);
		while ($row = $this->fetch_array($query)){
			$rt[] = $row;
			$rto[] = (object)$row;
		}
		$this->free_result($query);

		//
		$k = @current(array_keys(current($rt)));
		if($k){
			foreach ($rt as $r){
				$rrt[$r[$k]] = (object)$r;
			}
		}
		return (count($rrt) == count($rt))?$rrt:$rto;
	}
	
	function get_all_arr($sql, $type = '', $expires = 3600, $dbname = ''){
		$rt = array();
		$query = $this->query($sql, $type, $expires, $dbname);
		while ($row = $this->fetch_array($query)){
			$rt[] = $row;
		}
		$this->free_result($query);
	
		return $rt;
	}

	function get_array_keyvalue($sql, $type = '', $expires = 3600, $dbname = ''){
		$query = $this->query($sql, $type, $expires, $dbname);
		$rt = array();
		while ($row = $this->fetch_array($query,MYSQL_NUM)){
			$rt[$row[0]] = $row[1];
		}
		$this->free_result($query);
		return $rt;
	}

	function get_result($sql, $type = '', $expires = 3600, $dbname = ''){
		$query = $this->query($sql, $type, $expires, $dbname);
		$element = $this->result($query,0);
		$this->free_result($query);
		return $element;
	}

	function get_result_arr($sql, $type = '', $expires = 3600, $dbname = ''){
		$query = $this->query($sql, $type, $expires, $dbname);
		while ($row = $this->fetch_row($query)){
			$rt[] = $row[0];
		}
		$this->free_result($query);
		return (array)$rt;
	}

	function fetch_array($query, $result_type = MYSQL_ASSOC){
		return $this->caching ? $this->_fetch_array($query) : mysql_fetch_array($query, $result_type);
	}
	function fetch_object($query){
		return $this->caching ? $this->_fetch_object($query) : mysql_fetch_object($query);
	}

	function affected_rows(){
		return mysql_affected_rows($this->connid);
	}

	function num_rows($query){
		return $this->caching ? $this->_num_rows($query) : mysql_num_rows($query);
	}

	function num_fields($query){
		return mysql_num_fields($query);
	}

	function result($query, $row){
		return @mysql_result($query, $row);
	}

	function list_tables($query){
		return mysql_list_tables($query);
	}

	function free_result($query){
		if($this->caching==1) $this->result = array();
		else @mysql_free_result($query);
	}

	function insert_id(){
		return mysql_insert_id($this->connid);
	}

	function fetch_row($query){
		return mysql_fetch_row($query);
	}

	function version(){
		return mysql_get_server_info($this->connid);
	}

	function close(){
		return mysql_close($this->connid);
	}

	function _query_cache($sql){
		$this->cache_id = md5($sql);
		$this->result = array();
		$this->cursor = 0;
		$this->cache_file = $this->_get_file();
		if($this->_is_expire()){
			$this->result = $this->_get_array($sql);
			$this->_save_result();
		}
		else
		{
			$this->result = $this->_get_result();
		}
		return $this->result;
	}

	function _fetch_array($result = array()){
		if($result) $this->result = $result;
		return isset($this->result[$this->cursor]) ? $this->result[$this->cursor++] : FALSE;
	}

	function _num_rows($result = array()){
		if($result) $this->result = $result;
		return count($this->result);
	}

	function _save_result(){
		if(!is_array($this->result)) return FALSE;
		dir_create(dirname($this->cache_file));
		file_put_contents($this->cache_file, "<?php\n return ".var_export($this->result, TRUE).";\n?>");
		@chmod($this->cache_file, 0777);
	}

	function _get_array($sql){
		$this->cursor = 0;
		$arr = array();
		//////////
		$_time = explode(' ', microtime());
		$sql_starttime = $_time[1] + $_time[0];
		//////////
		$result = mysql_unbuffered_query($sql, $this->connid);
		while($row = mysql_fetch_assoc($result)){
			$arr[] = $row;
		}
		$this->free_result($result);
		$this->querynum++;
		//////////
		$_time = explode(' ', microtime());
		$sql_endtime = $_time[1] + $_time[0];
		$sql_costtime = $sql_endtime-$sql_starttime;
		if ($sql_costtime > 0.01){
			$this->querysql[] = ''.sprintf('%.4f',$sql_costtime).':'.$sql;
		}else {
			$this->querysql[] = sprintf('%.4f',$sql_costtime).':'.$sql;
		}
		//////////
		return $arr;
	}

	function _get_result(){
		return include $this->cache_file;
	}

	function _is_expire(){
		return !file_exists($this->cache_file) || ( TIME > @filemtime($this->cache_file) + $this->expires );
	}

	function _get_file(){
		return DB_CACHEDIR.substr($this->cache_id, 0, 2).'/'.$this->cache_id.'.php';
	}

	function error(){
		return @mysql_error($this->connid);
	}

	function errno(){
		return intval(@mysql_errno($this->connid)) ;
	}

	function halt($message = '', $sql = ''){
		exit('MySQL Query:'.$sql.' <br> MySQL Error:'.$this->error().' <br> MySQL Errno:'.$this->errno().' <br> Message:'.$message);
	}
	function escape($string){
		if(!is_array($string)) return str_replace(array('\n', '\r'), array(chr(10), chr(13)), mysql_escape_string(preg_replace($this->search, $this->replace, $string)));
		foreach((array)$string as $key=>$val) $string[$key] = $this->escape($val);
		return $string;
	}

	function gen_query($table, $what, $where = false, $order=false, $limit = false, $joins=false){
		if(is_array($what)){
			$what = implode(',',$what);
		}
		$qry = 'select '.$what.' from '. $table .( $where? " where 1".implode(' ',$where):'').($limit?' limit '.$limit:'');
		return $qry;
	}
}
?>