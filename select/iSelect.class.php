<?php

namespace db_utils\select;

abstract class iSelect implements \IteratorAggregate, \Countable {
	
	public abstract function free();

}
