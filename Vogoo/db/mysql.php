<?php
/***************************************************************************
 *                                 mysql.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: mysql.php,v 1.2 2005/04/05 09:44:05 sdroux Exp $
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

// 
// Important notice: file modified by Stephane DROUX
// The original version of the file provided by The phpBB Group can be downloaded from http://www.phpbb.com
//

class vg_sql_db
{

	var $db_connect_id;
	var $query_result;
	var $row = array();
	var $aResult = array();
	var $rowset = array();
	var $num_queries = 0;

	//
	// Constructor
	//
	function vg_sql_db($sqlserver, $sqluser, $sqlpassword, $database, $persistency = true)
	{
		//echo("Connecting..");
		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->password = $sqlpassword;
		$this->server = $sqlserver;
		$this->dbname = $database;

		if($this->persistency)
		{
			$this->db_connect_id = mysqli_pconnect($this->server, $this->user, $this->password);
		}
		else
		{
			$this->db_connect_id = mysqli_connect($this->server, $this->user, $this->password);
		}
		if($this->db_connect_id)
		{
			//echo("Connected..");
			if($database != "")
			{
				$this->dbname = $database;
				$dbselect = mysqli_select_db($this->db_connect_id, $this->dbname);
				if(!$dbselect)
				{
					//echo("could not select db");
					mysqli_close($this->db_connect_id);
					$this->db_connect_id = $dbselect;
				}
			}
			return $this->db_connect_id;
		}
		else
		{
			echo("Not connected..");
			return false;
		}
	}

	//
	// Other base methods
	//
	function sql_close()
	{
		if($this->db_connect_id)
		{
			if($this->query_result)
			{
				@mysqli_free_result($this->query_result);
			}
			$result = @mysqli_close($this->db_connect_id);
			return $result;
		}
		else
		{
			return false;
		}
	}

	//
	// Base query method
	//
	function sql_query($query = "", $transaction = FALSE)
	{
		// Remove any pre-existing queries
		unset($this->query_result);
		if($query != "")
		{
			$this->num_queries++;
			//echo($query);
			$this->query_result = mysqli_query($this->db_connect_id, $query);
			//var_dump(mysqli_fetch_assoc($this->query_result));
		}
		if($this->query_result)
		{
			unset($this->row[$this->query_result]);
			unset($this->rowset[$this->query_result]);
			return $this->query_result;
		}
		else
		{
			return ( $transaction == END_TRANSACTION ) ? true : false;
		}
	}
	
	function sql_query2($query = "", $transaction = FALSE)
	{
		// Remove any pre-existing queries
		unset($this->query_result);
		if($query != "")
		{
			$this->num_queries++;
			//echo($query);
			$this->query_result = mysqli_query($this->db_connect_id, $query);
			//var_dump(mysqli_fetch_assoc($this->query_result));
		}
		if($this->query_result)
		{
			//unset($this->row[$this->query_result]);
			//unset($this->rowset[$this->query_result]);
			//var_dump(mysqli_fetch_array($this->query_result));
			//echo("bla27");
			if($this->query_result === false) return false;
			if($this->query_result === true) return true;
			
			if(mysqli_num_rows($this->query_result) == 1){
				$aResult = mysqli_fetch_array($this->query_result); 
				$this->aResult = $aResult;
				//var_dump($aResult);
				return $aResult;
			}
			else{
				$aResult = array();
				while($aCurResult = mysqli_fetch_array($this->query_result)){
					array_push($aResult, $aCurResult);
					
				}
				$this->aResult = $aResult;
				return $aResult;
			}
		}
		else
		{
			//echo("basdsf");
			return ( $transaction == END_TRANSACTION ) ? true : false;
		}
	}

	//
	// Other query methods
	//
	function sql_numrows($query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			$result = @mysqli_num_rows($query_id);
			echo("num: ");
			var_dump($result);
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_affectedrows()
	{
		if($this->db_connect_id)
		{
			$result = @mysqli_affected_rows($this->db_connect_id);
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_numfields($query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			$result = @mysqli_num_fields($query_id);
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_fieldname($offset, $query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			$result = @mysqli_field_name($query_id, $offset);
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_fieldtype($offset, $query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			$result = @mysqli_field_type($query_id, $offset);
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_fetchrow($query_id = 0)
	{
		//echo("queryid: "); 
			//var_dump($query_id);
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			//echo("queryid: "); 
			//var_dump($query_id);
			$this->row[$query_id] = mysqli_fetch_array($query_id);
			//echo("<br/>row FETCH: ");
			//var_dump($this->row[$query_id]);
			return $this->row[$query_id];
		}
		else
		{
			return false;
		}
	}
	function sql_fetchrowset($query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			unset($this->rowset[$query_id]);
			unset($this->row[$query_id]);
			while($this->rowset[$query_id] = mysqli_fetch_array($query_id))
			{
				$result[] = $this->rowset[$query_id];
			}
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_fetchfield($field, $rownum = -1, $query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			if($rownum > -1)
			{
				$result = mysqli_result($query_id, $rownum, $field);
			}
			else
			{
				if(empty($this->row[$query_id]) && empty($this->rowset[$query_id]))
				{
					if($this->sql_fetchrow())
					{
						$result = $this->row[$query_id][$field];
					}
				}
				else
				{
					if($this->rowset[$query_id])
					{
						$result = $this->rowset[$query_id][$field];
					}
					else if($this->row[$query_id])
					{
						$result = $this->row[$query_id][$field];
					}
				}
			}
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_rowseek($rownum, $query_id = 0){
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			$result = @mysqli_data_seek($query_id, $rownum);
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_nextid(){
		if($this->db_connect_id)
		{
			$result = @mysqli_insert_id($this->db_connect_id);
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_freeresult($query_id = 0){
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}

		if ( $query_id )
		{
			unset($this->row[$query_id]);
			unset($this->rowset[$query_id]);

			@mysqli_free_result($query_id);

			return true;
		}
		else
		{
			return false;
		}
	}
	function sql_error($query_id = 0)
	{
		$result["message"] = @mysqli_error($this->db_connect_id);
		$result["code"] = @mysqli_errno($this->db_connect_id);

		return $result;
	}

} // class sql_db

?>
