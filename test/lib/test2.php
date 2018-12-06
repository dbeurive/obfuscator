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



class TestMe {

    private $__p = 0;

    public function reflect($method) {
        $rm = new ReflectionMethod($this, $method);
        if (!$rm->isPublic()) {
            $rm->setAccessible(true);
        }
        $rm->invoke($this);
    }

    private function __m1() {
        print(__METHOD__);
    }

    public function m2() {
        $this->reflect('__m1');
        $name = '__p';
        $this->$name = 10;
    }

}

$c = new TestMe();
$c->m2();