<?php

class myclass {
    
    var $testvar = 555;

    function set_testvar($newvar) {
        $this->testvar = isset($newvar) ? $newvar : $this->testvar;
    }

    function get_testvar() {
        return $this->testvar;
    }

}