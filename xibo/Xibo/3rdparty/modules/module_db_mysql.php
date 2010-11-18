<?php
/*
 * Xibo - Digitial Signage - http://www.xibo.org.uk
 * Copyright (C) 2006,2007,2008 Daniel Garner and James Packer
 *
 * This file is part of Xibo.
 *
 * Xibo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version. 
 *
 * Xibo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Xibo.  If not, see <http://www.gnu.org/licenses/>.
 */
defined('XIBO') or die("Sorry, you are not allowed to directly access this page.<br /> Please press the back button in your browser.");
 
class database 
{

    public $error_text;

    //connects to the database
    function __construct() 
	{
		
    }
    
    function connect_db($dbhost, $dbuser, $dbpass) 
	{
    	//open the db link
        $dblink = mysql_connect($dbhost, $dbuser, $dbpass);
        
        if(!$dblink) 
		{
        	return false;
        }
		
        return true;	
    }
    
    function select_db($dbname) 
	{
    	//select out the correct db name
        if(!mysql_select_db($dbname)) return false;
        
        return true;
    }

    /**
     * Runs a query on the database
     * @param <string> $SQL
     * @return <type>
     */
    function query($SQL) 
    {
        if ($SQL == '')
        {
            $this->error_text = 'No SQL provided';
            return false;
        }

        
        if(!$result = mysql_query($SQL)) 
	{
            $this->error_text = 'The query [' . $SQL . '] failed to execute';
            return false;
        }
        /*else
	{
            Debug::LogEntry($this, 'audit', 'Running SQL: [' . $SQL . ']', '', 'query');
        }*/
        return $result;
    }
    
    function insert_query($SQL) 
	{
    	//executes a SQL query and returns the ID of the insert
    	if(!$result = mysql_query($SQL)) 
		{
            $this->error_text="The query [".$SQL."] failed to execute";
            return false;
        }
        else 
		{
            return mysql_insert_id();
        }
    }

    //gets the current row from the result object
    function get_row($result) 
	{
        return mysql_fetch_row($result);
    }
	
	function get_assoc_row($result) 
	{
        return mysql_fetch_assoc($result);
    }


    //gets the number of rows
    function num_rows($result) 
	{
        return mysql_num_rows($result);
    }

    //gets the number of fields
    function num_fields($result) 
	{
        return mysql_num_fields($result);
    }
    
    function escape_string($string) 
	{
    	return mysql_real_escape_string($string);
    }

    /**
     * Gets a Single row using the provided SQL
     * Returns false if SQL error or no records found
     * @param <string> $SQL
     * @param <bool> $assoc
     */
    public function GetSingleRow($SQL, $assoc = true)
    {
        if (!$result = $this->query($SQL))
            return false;

        if ($this->num_rows($result) == 0)
        {
            $this->error_text = 'No results returned';
            return false;
        }

        if ($assoc)
        {
            return $this->get_assoc_row($result);
        }
        else
        {
            return $this->get_row($result);
        }
    }

    /**
     * Gets a single value from the provided SQL
     * @param <string> $SQL
     * @param <string> $columnName
     * @param <int> $dataType
     * @return <type>
     */
    public function GetSingleValue($SQL, $columnName, $dataType)
    {
        if (!$row = $this->GetSingleRow($SQL))
            return false;

        if (!isset($row[$columnName]))
        {
            $this->error_text = 'No such column';
            return false;
        }

        return Kit::ValidateParam($row[$columnName], $dataType);
    }

    //returns the error text to display
    function error() 
	{
        try 
		{
            $this->error_text .= "<br />MySQL error: ".mysql_error();
            return $this->error_text;
        }
        catch (Exception $e) 
		{
            echo $e->getMessage();
        }
    }
}

?>
