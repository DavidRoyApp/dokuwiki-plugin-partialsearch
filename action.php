<?php
/**
 * Partial Search Plugin
 *
 * @author David Roy <davidroyapp@gmail.com>
 */

if(!defined('DOKU_INC')) die();

if(!defined('WILDCARD')) define ('WILDCARD', '*');
require_once DOKU_INC.'inc/fulltext.php';
require_once DOKU_INC.'inc/utf8.php';

class action_plugin_partialsearch extends DokuWiki_Action_Plugin {

    function register(Doku_Event_Handler $controller) {
        $controller->register_hook('SEARCH_QUERY_FULLPAGE', 'BEFORE', $this, 'partial_search_before');
        $controller->register_hook('SEARCH_QUERY_FULLPAGE', 'AFTER', $this, 'partial_search_after');
        $controller->register_hook('SEARCH_QUERY_PAGELOOKUP', 'BEFORE', $this, 'pagelookup_before');
        $controller->register_hook('FULLTEXT_SNIPPET_CREATE', 'AFTER',  $this, 'snippet_create_after');
    }

    function partial_search_before(&$event, $args) {
        global $conf;

        if ($this->getConf('enablepartialsearch')) {
            $this->_partial_search($event, $args, WILDCARD);
        }
    }

    function partial_search_after(&$event, $args) {
        global $conf;

        if ($this->getConf('enablepartialsearch')) {
            $this->_partial_search($event, $args, '');
        }
		
        if ($this->getConf('enablesearchlookupsnippet')) {
            $data= array();
            $data['id']= $event->data['query'];
            $data['in_ns']= true;
            $data['in_title']= true;
            $data['after']= null;
            $data['before']= null;
            $data['has_titles']= true; // for plugin backward compatibility check
            $pageLookup= _ft_pageLookup($data);
            foreach($pageLookup as $key=>$value){
                if (!isset($event->result[$key])){
                    $event->result[$key]= $this->getLang('titlehas'); //if assign value 0 then it won't generate snippet
                }
            }
        }
    }

    function _partial_search(&$event, $args, $surrounding='') {
        $arr= explode(' ', $event->data['query']);
        array_walk($arr, function(&$value) use ($surrounding) { $value = $surrounding . trim($value, WILDCARD) . $surrounding; });
        $event->data['query']= implode(' ', $arr);
    }

    function pagelookup_before(&$event, $args) {
        global $conf;

        if ($this->getConf('enablesearchlookupsnippet') && $event->canPreventDefault){
            $event->stopPropagation();
            $event->preventDefault();
            $event->result=[]; // Empty page lookup result because they've been included in QUERY_FULLPAGE
        }
    }

    function snippet_create_after(&$event, $args) {
        global $conf;

        //$id, $text, $highlight, $snippet
        extract($event->data, EXTR_REFS);

        if ($this->getConf('addtitletosnippet')) {
            $title = p_get_first_heading($id);
            if (isset($title) && trim($title)!==''){
                $snippet = $title . '<br/>' . $snippet;
            }
        }

        if ($this->getConf('enablesearchlookupsnippet') && (!isset($snippet) || trim($snippet)==='')) {
            $snippet = utf8_substr($text, 0, 250);
        } 		
    }

}
