<?php

/**
 * @desc 以memcache方式存储session 驱动类
 * @author liuyaya 2015.03.25
 */

defined('THINK_PATH') or exit();
/*
 * 可选配置项
 * 'SESSION_EXPIRE' => 1440,//有效期 单位秒
 * 'SESSION_HOST' => '127.0.0.1',//存储session的memcache服务器地址
 * 'SESSION_PORT' => 11211,//存储session的memcache服务端口
 */

class SessionMemcache {
    
    private $lifetime; //session有效时间
    private $mem; //memcache对象
    
    public function open($save_path,$sess_name){
        $this->lifetime = C('SESSION_EXPIRE')?C('SESSION_EXPIRE'):ini_get('session.gc_maxlifetime');
        $this->mem = new Memcache();
        if(C('SESSION_HOST')){
            $serv = C('SESSION_HOST');
            $port = C('SESSION_PORT')?C('SESSION_PORT'):11211;
        }else{
            defined('MASTER_MC_SERV') or define('MASTER_MC_SERV','127.0.0.1:11211');
            list($serv,$port) = explode(':',MASTER_MC_SERV);
            $serv = empty($serv) ? '127.0.0.1' : $serv;
            $port = empty($port) ? 11211 : $port;
        }
        $this->mem->connect($serv,$port);
    }
    
    /**
     * @desc 关闭session
     */
    public function close(){
        $this->mem->close();
    }
    
    /**
     * @desc 读取session
     * @param string $sess_id
     */
    public function read($sess_id){
        $res = $this->mem->get($sess_id);
        if($res){
            return $res;
        }
        return '';
    }
    
    /**
     * @desc 写入session
     * @param string $sess_id
     * @param string $sess_data
     */
    public function write($sess_id,$sess_data){
        $res = $this->mem->set($sess_id, $sess_data,0,$this->lifetime);
        if($res){
            return true;
        }
        return false;
    }
    
    /**
     * @desc 删除session
     * @param string $sess_id
     */
    public function destroy($sess_id){
        $this->mem->delete($sess_id);
    }
    
    /**
     * @desc session 垃圾回收
     * @param string $sess_lifetime
     */
    public function gc($sess_lifetime){
        return true;
    }
    
    public function execute(){
        session_set_save_handler(
            array(&$this,'open'),
            array(&$this,'close'),
            array(&$this,'read'),
            array(&$this,'write'),
            array(&$this,'destroy'),
            array(&$this,'gc')
        );
    }
    
}