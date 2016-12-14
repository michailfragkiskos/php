<?php

  /*
   * Description of Abstract
   * Copyright (c) 2013 - 2016 Michail Fragkiskos 
   * 
   * This is free software; you can redistribute it and/or
   * modify it under the terms of the GNU Lesser General Public
   * License as published by the Free Software Foundation; either
   * version 2.1 of the License, or (at your option) any later version.
   *
   * This Framework is distributed in the hope that it will be useful,
   * but WITHOUT ANY WARRANTY; without even the implied warranty of
   * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
   * Lesser General Public License for more details. 
   * @category   PhP
   * @copyright  Copyright (c) 2013 - 2016 Michail Fragkiskos (http://www.fragkiskos.uk)
   * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
   * @version    0.1.5, 2016-1-11  
   */

  abstract class PhpExcel_Abstract {



      /**
       * Constant indicating no error.
       * @var integer
       */
      const ERROR_OK = 0;

      /**
       * Constant indicating that there was an IO error reading the file
       * @var integer
       */
      const ERROR_IO_ERROR = 1;

      /**
       * Constant indicating that an attempt was made to read past the end of the
       * current worksheet
       * @var integer
       */
      const ERROR_END_OF_WORKSHEET = 2;

      /**
       * Constant indicating that an attempt was made to read past the end of the
       * file
       * @var integer
       */
      const ERROR_END_OF_FILE = 3;

      /**
       * Constant indicating that the specified file could not be opened
       * @var integer
       */
      const ERROR_FILE_NOT_FOUND = 4;

      /**
       * Constant indicating that the specified filetype is not handled
       * @var integer
       */
      const ERROR_FILE_TYPE_NOT_HANDLED = 5;

      /**
       * Constant indicating that no file has been opened
       * @var integer
       */
      const ERROR_FILE_NOT_OPENED = 6;

      /**
       * Constant indicating that the specified file is corrupted
       * @var integer
       */
      const ERROR_FILE_CORRUPTED = 7;

      /**
       * Constant indicating that another error has occurred
       * @var integer
       */
      const ERROR_OTHER = 99;



      /**
       * The code of the last error that occurred.
       * @var integer
       */
      private $_error_code = self::ERROR_OK;

      /**
       * The message of the last error that occurred.
       * @var string
       */
      private $_error_message = null;



      /**
       * Constructor.
       */
      public function __construct () {
          
      }



      /**
       * Destructor
       */
      public function __destruct () {
          
      }



      /**
       * Return the last error that occurred
       * @return integer the last error that occurred or 0
       */
      public function getErrorCode () {
          /* Return the error code. */
          return $this->_error_code;
      }



      /**
       * Return the human readable error message for the last error that occurred.
       * @return string the last error that occurred or null
       */
      public function getErrorMessage () {
          /* If we have no error, return nothing. */
          if (!$this->_error_code) return null;

          /* If we have an error message, return it. */
          if (!is_null($this->_error_message))
                  return $this->_error_message;

          /* Otherwise, generate a default message. */
          switch ($this->_error_code)
          {
              case self::ERROR_OK : return null;
              case self::ERROR_IO_ERROR : return 'IO error';
              case self::ERROR_END_OF_WORKSHEET : return 'end of worksheet';
              case self::ERROR_END_OF_FILE : return 'end of file reached';
              case self::ERROR_FILE_NOT_FOUND : return 'file not found';
              case self::ERROR_FILE_TYPE_NOT_HANDLED : return 'file type not recognised';
              case self::ERROR_FILE_NOT_OPENED : return 'file not opened';
              case self::ERROR_FILE_CORRUPTED : return 'file corrupted';
              case self::ERROR_OTHER : return 'unknown error';
              default : return 'unknown error';
          }
      }



      /**
       * Clear the last error.
       */
      public function clearError () {
          /* Clear the error. */
          $this->setError(self::ERROR_OK);
      }



      /**
       * Set the last error that occurred.
       * @param integer $error_code the error code to store.
       * @return boolean always returns false.
       */
      protected function setError ($error_code, $error_message = null) {
          /* Store the error. */
          $this->_error_code = $error_code;
          $this->_error_message = $error_message;

          /* Return false. */
          return false;
      }



      /**
       * Return true if this class can possibly handle files of the given
       * file.
       * @param string $filename the name of the file to open
       * @param string $path the path of the directory that contains the file
       * @return boolean true on success or false on error.
       */
      abstract public function canHandleFile ($filename, $path);



      /**
       * Open a file for processing.
       * @param string $filename the name of the file to open
       * @param string $path the path of the directory that contains the file
       * @return boolean true on success or false on error.
       */
      abstract public function openFile ($filename, $path);



      /**
       * Close the currently open file.
       */
      abstract public function closeFile ();



      /**
       * Return if there is a currently open file.
       * @return boolean true if there is an open file.
       */
      abstract public function isFileOpen ();



      /**
       * Return if there a current row to read (no error or EOF)
       * @return boolean true if there is a current row to read.
       */
      abstract public function hasCurrentRowData ();



      /**
       * Return an array containing the current row of the tabular data.
       * @return mixed array of data or false on error
       */
      abstract public function getCurrentRowData ();



      /**
       * Advance to the next row of the tabular data.
       * @return boolean true on success or false on error (EOF is not an error)
       */
      abstract public function gotoNextRow ();



      /**
       * Return if there a worksheet (no error or EOF)
       * @return boolean true if there is a current worksheet to read from.
       */
      abstract public function hasCurrentWorksheet ();



      /**
       * Advance to the next worksheet of the tabular data.
       * @return boolean true on success or false on error (end of file is not an error)
       */
      abstract public function gotoNextWorksheet ();



      /**
       * Advance to the named worksheet of the tabular data.
       * @return boolean true on success or false on error (worksheet not found is not an error)
       */
      abstract public function gotoNamedWorksheet ($worksheet_name);



      /**
       * Get the name of the current worksheet
       * @return string|boolean worksheet name or false on error
       */
      abstract public function getCurrentWorksheetName ();



      /**
       * Get the index of the current worksheet
       * @return integer|boolean worksheet index or false on error
       */
      abstract public function getCurrentWorksheetIndex ();



      /**
       * Get the index of the current row
       * @return integer|boolean row index or false on error
       */
      abstract public function getCurrentRowIndex ();



      /**
       * Return an opaque location object that can be used to move back to the
       * current location
       * @return mixed opaque (but serializable) location or false on error
       */
      abstract public function getCurrentLocation ();



      /**
       * Return to the location given by a location object
       * @param mixed the location to return to
       * @return boolean true on success or false on error (EOF is not an error)
       */
      abstract public function gotoLocation ($location);





  }

  