<?php
/**
 * Partial Search Plugin
 *
 * @author David Roy <davidroyapp@gmail.com>
 */

if(!defined('DOKU_INC')) die();

if(!defined('DOKU_LF')) define ('DOKU_LF', "\n");
require_once DOKU_INC.'inc/parser/renderer.php';
require_once DOKU_INC.'inc/parser/xhtml.php';

class renderer_plugin_partialsearch extends Doku_Renderer_xhtml {

    function getFormat(){
        return 'xhtml';
    }

    function canRender($format){
        return ($format=='xhtml');
    }

    function _simpleTitle($name) {
        global $conf;
        $name= parent::_simpleTitle($name);

        if ($this->getConf('replaceunderscores')) {
            return $this->_replaceChars($name);
        } else {
            return $name;
        }
    }

    function _getLinkTitle($title, $default, &$isImage, $id = null, $linktype = 'content') {
        global $conf;
        $title= parent::_getLinkTitle($title, $default, $isImage, $id, $linktype);

        if ($this->getConf('replaceunderscores') && $id) {
            return $this->_replaceChars(getNS($id)) . ' ' . $title;
        }else{
            return $title;
        }
    }

    /**
     * Similar to XBR Plugin: Replaces \n in .txt files with <br/>
     * Needed because there can be only 1 xhtml renderer (cannot use XBR plugin with this one)
     */
    function cdata($text) {
        global $conf;

        if ($this->getConf('userawreturns')) {
            $this->doc .= str_replace(DOKU_LF,"<br />".DOKU_LF,$this->_xmlEntities($text));
        } else {
            parent::cdata($text);
        }
    }

    function _replaceChars($text) {
        $text = strtr($text, '_', ' ');
        return $text;
    }

}
