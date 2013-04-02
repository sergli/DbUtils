<?php

namespace db_utils\select;

abstract class iSelect implements \IteratorAggregate {
	
	public abstract function free();

}
