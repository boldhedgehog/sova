<?php

class SovaSmarty extends Smarty
{
    protected $_headHtml = array();

    /**
     *
     * @param string $html
     *
     * @return SovaSmarty
     */
    public function addHeadHtml($html)
    {
        $this->_headHtml[] = $html;

        return $this;
    }

    public function fetch($template = null, $cache_id = null, $compile_id = null, $parent = null, $display = false, $merge_tpl_vars = true, $no_output_filter = false)
    {
        $this->assignByRef('head_html', $this->_headHtml);

        return parent::fetch($template, $cache_id, $compile_id, $parent, $display, $merge_tpl_vars, $no_output_filter);
    }
}