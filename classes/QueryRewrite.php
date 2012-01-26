<?php


	class QueryRewrite{
		private $sql = null;
		private $type = 0;
		
		const UNKNOWN = 0;
		const SELECT  = 1;
		const DELETE  = 2;
		const INSERT  = 3;
		const UPDATE  = 4;
		const ALTER   = 5;
		const DROP    = 6;
		const CREATE  = 7;
		
		public function __construct($sql) {
			$this->sql = trim($sql);
			$this->figureOutType();
		}
		
		function figureOutType(){
			if (preg_match('/^DELETE\s+FROM\s/', $this->sql))
				$this->type = self::DELETE;
			elseif (preg_match('/^INSERT\s+INTO\s/', $this->sql))
				$this->type = self::INSERT;
			else
				$this->type = self::UNKNOWN;
		}
		
		function toSelect() {
			switch ($this->type) {
				case self::SELECT:
					return $this->sql;
				case self::DELETE:
					return preg_replace('/^DELETE\s+FROM\s/', 'SELECT 0 FROM ', $this->sql);
			}
			return null;
		}
		
		function asExplain() {
			switch ($this->type) {
				case self::SELECT:
					$sql = $this->sql;
					break;
				case self::DELETE:
					$sql = $this->toSelect();
					break;
				default:
					return null;
			}
			return "EXPLAIN $sql";
		}
		
		function asExtendedExplain() {
			$sql = $this->asExplain();
			if (is_null($sql))
				return null;
			$sql = preg_replace('/^EXPLAIN /', 'EXPLAIN EXTENDED ', $sql);
			return $sql;
		}
	}