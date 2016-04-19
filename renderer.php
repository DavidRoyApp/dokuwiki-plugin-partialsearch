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
        $name= parent::_simpleTitle($name);
        return $this->_replaceChars($name);
    }

    function _getLinkTitle($title, $default, &$isImage, $id = null, $linktype = 'content') {
        $title= parent::_getLinkTitle($title, $default, $isImage, $id, $linktype);
        if ($id){
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
        $text= str_replace(DOKU_LF, '<br />'.DOKU_LF, $text);
        parent::cdata($text);
    }

    function _replaceChars($text) {
        $text = strtr($text, '_', ' ');
        return $text;
    }

}
