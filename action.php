<?php
/**
 * Partial Search Plugin
 *
 * @author David Roy <davidroyapp@gmail.com>
 */

if(!defined('DOKU_INC')) die();

require_once DOKU_INC.'inc/fulltext.php';
require_once DOKU_INC.'inc/utf8.php';

class action_plugin_partialsearch extends DokuWiki_Action_Plugin {

    function register(Doku_Event_Handler $controller) {
        $controller->register_hook('SEARCH_QUERY_FULLPAGE', 'BEFORE', $this, 'partial_search_before');
        $controller->register_hook('SEARCH_QUERY_FULLPAGE', 'AFTER', $this, 'partial_search_after');
        $controller->register_hook('SEARCH_QUERY_PAGELOOKUP', 'BEFORE', $this, 'disable_search_lookup');
        $controller->register_hook('FULLTEXT_SNIPPET_CREATE', 'AFTER',  $this, 'snippet_create_after');
    }

    function partial_search_before(&$event, $args) {
        $this->_partial_search($event, $args, '*');
    }

    function partial_search_after(&$event, $args) {
        $this->_partial_search($event, $args, '');
		
		$data= array();
		$data['id']= $event->data['query'];
		$data['in_ns']= true;
		$data['in_title']= true;
		$data['has_titles']= true;
		$pageLookup= _ft_pageLookup($data);
		foreach($pageLookup as $key=>$value){
			if (!isset($event->result[$key])){
				$event->result[$key]='El título tiene'; //if assign 0 then it won't generate snippet
			}
		}
    }

    function _partial_search(&$event, $args, $surrounding='') {
        global $conf;

        if ($this->getConf('enablepartialsearch')) {
            $arr= explode(' ', $event->data['query']);
            array_walk($arr, function(&$value) use ($surrounding) { $value = $surrounding . trim($value, '*') . $surrounding; });
            $event->data['query']= implode(' ', $arr);
        }
    }

    function disable_search_lookup(&$event, $args) {
        global $conf;

        if ($this->getConf('disablesearchlookup')){
            $event->stopPropagation();
            $event->preventDefault();
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
		
		if (!isset($snippet) || trim($snippet)==='') {
			$snippet = utf8_substr($text, 0, 250);
		} 		
    }

}
