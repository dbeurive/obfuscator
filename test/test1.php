<?php

//	=========================================================================================
//	
//	Copyright (c) 2016 Kertios All rights reserved
//	
//	Avertissement : ce logiciel est protégé par la loi relative au droit d'auteur et par les
//	conventions internationales. Toute reproduction, modification ou distribution partielle
//	ou totale de ce logiciel, par quelque moyen que ce soit, est strictement interdite. Toute
//	personne ne respectant pas ces dispositions se rendra coupable du délit de contrefaçon et
//	sera passible des sanctions pénales prévues par la loi.
//	
//	=========================================================================================



/**
 * Class Test
 */

class TestMe
{
    /**
     * @var int|null A comment.
     */

    private $__var1 = null;
    public $var2 = null;


    // Another comment
    public function __construct() {
        $this->__var1 = 10;
        fwrite(STDOUT, $this->__var1 . $this->var2 . PHP_EOL);
    }

    private function __go($p1, $p2) {
        $local = 3+4;
        print $p1 . $p2 . $this->__var1;
    }

    public function go() {
        print $this->__go(1, $this->__var1);
    }

}