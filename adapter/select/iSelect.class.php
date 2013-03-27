<?php

namespace db_utils\adapter\select;

abstract class iSelect implements \IteratorAggregate {
	
	public abstract function free();

}
