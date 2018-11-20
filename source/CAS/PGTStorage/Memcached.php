<?php
class CAS_PGTStorage_Memcached extends CAS_PGTStorage_AbstractStorage
{
    var $_cache;

    function __construct($servers, $ncopies = 2, $expiration = 30){
        $this->_cache = new Memcached();
        $this->_cache->addServers($servers);
        $this->_ncopies = $ncopies;
        $this->_expiration = $expiration;
    }
    function init() {
        parent::init();
    }

    function write($pgt,$pgt_iou) {
        for ($i = 0; $i < $this->_ncopies; $i++){
            $this->_cache->set($pgt_iou.'-'.$i, $pgt, $this->_expiration);
        }
    }

    function read($pgt_iou) {
        for ($i = 0; $i < $this->_ncopies; $i++){
            $pgt = $this->_cache->get($pgt_iou.'-'.$i);
            if ($pgt){
                return $pgt;
            }
        }
	syslog(LOG_ERR, "No se ha encontrado PGT_IOU $pgt_iou");
    }
}
