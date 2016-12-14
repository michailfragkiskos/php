<?php

  /*
   * Description of Excel
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

  class PhpExcel_Excel extends PhpExcel_Abstract
                    implements PHPExcel_Reader_IReadFilter {



      /**
       * The number of rows read at a time.
       * @var integer
       */
      const ROWS_TO_LOAD = 1000;



      /**
       * The full path of the file we are reading.
       * @var string
       */
      private $_file_path = null;

      /**
       * The file reader used to parse the file we are reading
       * @var object
       */
      private $_file_reader = null;

      /**
       * An array of worksheet info about the file we are reading.
       * @var array
       */
      private $_worksheet_info = null;

      /**
       * The current location worksheet index.
       * @var integer
       */
      private $_location_worksheet = -1;

      /**
       * The current location row index.
       * @var integer
       */
      private $_location_row = -1;

      /**
       * The actual document.
       * @var object
       */
      private $_file_object = null;

      /**
       * The index of the worksheet that we have loaded.
       * @var integer
       */
      private $_loaded_worksheet = -1;

      /**
       * The first row that has been loaded.
       * @var integer
       */
      private $_loaded_row_start = -1;

      /**
       * The last row that has been loaded.
       * @var integer
       */
      private $_loaded_row_end = -1;



      /**
       * Constructor.
       */
      public function __construct () {
          /* Call the parent constructor. */
          parent::__construct();
      }



      /**
       * Destructor
       */
      public function __destruct () {
          /* Call the parent destructor. */
          parent::__destruct();
      }



      /**
       * Read filter for PHPExcel
       * @param string $column the column being read.
       * @param integer $row the row being read.
       * @param string $worksheetName the same of the worksheet being read.
       */
      public function readCell ($column, $row, $worksheetName = '') {
          /* If there is no read range, don't load the cell.  */
          if (($this->_loaded_row_start < 1) || ($this->_loaded_row_end <
                              1)) return false;

          /* If the row is in the read range, load the cell. */
          return (($this->_loaded_row_start <= $row) && ($row <= $this->_loaded_row_end));
      }



      /**
       * Return true if this class can possibly handle files of the given
       * file.
       * @param string $filename the name of the file to open
       * @param string $path the path of the directory that contains the file
       * @return boolean true on success or false on error.
       */
      public function canHandleFile ($filename, $path = null) {
          /* Create the full file path. */
          $file_path = (!is_null($path) ? $path . DIRECTORY_SEPARATOR : '') . $filename;

          /* If the file doesn't exist, return not handled. */
          if (!is_readable($file_path)) return false;

          /* Attempt to identify the file type. */
          $file_type = null;
          try
                    {
              $file_type = PHPExcel_IOFactory::identify($file_path);
                    }
          catch (PHPExcel_Reader_Exception $e)
                    {
              /* If we got an exception then we don't handle this filetype. */
              $file_type = null;
                    }

          /* Return if we got the type of the file. */
          return !is_null($file_type);
      }



      /**
       * Open a file for processing.
       * @param string $filename the name of the file to open
       * @param string $path the path of the directory that contains the file
       * @param array $options the special options for the loader
       * @param miked $location the location we want to start at
       * @return boolean true on success or false on error.
       */
      public function openFile ($filename, $path = null,
                                $options = array(), $location = null) {
          /* If there is a file already open, close it. */
          if ($this->isFileOpen()) $this->closeFile();

          /* Create the full file path. */
          $file_path = (!is_null($path) ? $path . DIRECTORY_SEPARATOR : '') . $filename;

          /* If the file doesn't exist, return not handled. */
          if (!is_readable($file_path)) return false;

          /* Attempt to identify the file type. */
          $file_type = null;
          try
                    {
              $file_type = PHPExcel_IOFactory::identify($file_path);

                    }
          catch (PHPExcel_Reader_Exception $e)
                    {
              /* If we got an exception then we don't handle this filetype. */
              $this->setError(self::ERROR_FILE_TYPE_NOT_HANDLED);
              return false;
                    }

          /* Attempt to create the reader for the file. */
          $file_reader = null;
          try
                    {
              $file_reader = PHPExcel_IOFactory::createReader($file_type);

                    }
          catch (PHPExcel_Reader_Exception $e)
                    {
              /* If we got an exception then we we can't find a reader. */
              $this->setError(self::ERROR_OTHER,
                              'unable to get file reader');
              return false;
                    }

          /* Set the options. */
          if (method_exists($file_reader, 'setDelimiter') && isset($options['csv_delimiter']))
                  $file_reader->setDelimiter($options['csv_delimiter']);
          if (method_exists($file_reader, 'setEnclosure') && isset($options['csv_enclosure']))
                  $file_reader->setEnclosure($options['csv_enclosure']);
          if (method_exists($file_reader, 'setLineEnding') && isset($options['csv_lineending']))
                  $file_reader->setLineEnding($options['csv_lineending']);
          if (method_exists($file_reader, 'setInputEncoding') && isset($options['csv_encoding']))
                  $file_reader->setInputEncoding($options['csv_encoding']);
          PHPExcel_Cell::setValueBinder(new PhpExcel_Excel_ValueBinder());

          /* Attempt to get the file info. */
          $worksheet_info = null;
          try
                    {
              $worksheet_info = array_values($file_reader->listWorksheetInfo($file_path));
                    }
          catch (PHPExcel_Reader_Exception $e)
                    {
              /* If we got an exception then we we can't find a reader. */
              $this->setError(self::ERROR_FILE_CORRUPTED,
                              'unable to get worksheet information');
              return false;
                    }

          /* Store the required information. */
          $this->_file_path = $file_path;
          $this->_file_reader = $file_reader;
          $this->_worksheet_info = $worksheet_info;

          /* If there is a location ... */
          if (!is_null($location)) {
              /* ...validate that this location is for this file. If not ... */
              if (!$this->valiateLocation($location)) {
                  /* ... drop it. */
                  $this->closeFile();
                  return false;
              }

              /* Store the location. */
              $this->_location_row = $location['row'];
              $this->_location_worksheet = $location['worksheet'];
          } else {
              /* Start on the first row of the first worksheet. */
              $this->_location_row = 1;
              $this->_location_worksheet = 0;
          }

          /* Store the currently loaded location. */
          $this->_loaded_worksheet = -1;
          $this->_loaded_row_start = -1;
          $this->_loaded_row_end = -1;

          /* Return success. */
          return true;
      }



      /**
       * Close the currently open file.
       */
      public function closeFile () {
          /* Clear all the data. */
          $this->_file_path = null;
          $this->_file_reader = null;
          $this->_worksheet_info = null;
          $this->_location_worksheet = -1;
          $this->_location_row = -1;
          $this->_loaded_worksheet = -1;
          $this->_loaded_row_start = -1;
          $this->_loaded_row_end = -1;
      }



      /**
       * Return if there is a currently open file.
       * @return boolean true if there is an open file.
       */
      public function isFileOpen () {
          /* Return that if have a file path. */
          return !is_null($this->_file_path) && is_readable($this->_file_path) &&
                              !is_null($this->_file_reader) && !is_null($this->_worksheet_info);
      }



      /**
       * Return if there is a current row to read (no error or EOF/W)
       * @return boolean true if there is a current row to read.
       */
      public function hasCurrentRowData () {
          /* If the file isn't open, there isn't a row to read. */
          if (!$this->isFileOpen()) return false;

          /* If the worksheet isn't valid, there isn't a row to read. */
          if (!isset($this->_worksheet_info[$this->_location_worksheet]))
                  return false;

          /* Get the worksheet info. */
          $worksheet_info = $this->_worksheet_info[$this->_location_worksheet];

          /* If the row isn't valid for the worksheet, there isn't one to read. */
          if (($this->_location_row < 1) || ($this->_location_row > $worksheet_info['totalRows']))
                  return false;

          /* Yes, there is a row to read. */
          return true;
      }



      /**
       * Return an array containing the current row of the tabular data.
       * @return mixed array of data or false on error
       */
      public function getCurrentRowData () {
          /* If the file isn't open, this is an error. */
          if (!$this->isFileOpen())
                  return $this->setError(self::ERROR_FILE_NOT_OPENED);

          /* If the worksheet isn't valid, there isn't a row to read. */
          if (!isset($this->_worksheet_info[$this->_location_worksheet]))
                  return $this->setError(self::ERROR_END_OF_FILE);

          /* Get the worksheet info. */
          $worksheet_info = $this->_worksheet_info[$this->_location_worksheet];

          /* If the row isn't valid for the worksheet, there isn't one to read. */
          if (($this->_location_row < 1) || ($this->_location_row > $worksheet_info['totalRows']))
                  return $this->setError(self::ERROR_END_OF_WORKSHEET);

          /* If the current location isn't currently loaded ... */
          if (($this->_file_object == null) || ($this->_loaded_worksheet <
                              0) || ($this->_location_worksheet != $this->_loaded_worksheet) ||
                              ($this->_loaded_row_start < 1) || ($this->_loaded_row_end <
                              1) || ($this->_location_row < $this->_loaded_row_start) ||
                              ($this->_loaded_row_end < $this->_location_row)) {
              /* ... we need to load the file again. Set the worksheet we need to load ... */
              $this->_file_reader->setLoadSheetsOnly(array(
                    $worksheet_info['worksheetName']));
              $this->_loaded_worksheet = $this->_location_worksheet;
              $this->_loaded_row_start = $this->_location_row;
              $this->_loaded_row_end = min($this->_location_row + self::ROWS_TO_LOAD,
                                           $worksheet_info['totalRows']);
              try
                              {
                  $this->_file_object = $this->_file_reader->load($this->_file_path);
                              }
              catch (PHPExcel_Reader_Exception $e)
                              {
                  /* If we got an exception then we we can't load the data. Set an error. */
                  $this->setError(self::ERROR_OTHER,
                                  'unable to get file reader');

                  /* Clear the loaded data ... */
                  $this->_loaded_worksheet = -1;
                  $this->_loaded_row_start = -1;
                  $this->_loaded_row_end = -1;

                  /* ... return failure. */
                  return false;
                              }
              \gc_collect_cycles();
          }

          /* Get the actual worksheet we need to read from. */
          $worksheet = $this->_file_object->getSheet(0);

          /* The row data. */
          $row_data = array();

          /* Iterate over the columns that we need to read. */
          for ($column_index = 0;
                                  $column_index < $worksheet_info['totalColumns'];
                                  $column_index++)
                    {
              /* ... get the data for the cell. */
              $cell = $worksheet->getCellByColumnAndRow($column_index,
                                                        $this->_location_row);

              /* If the cell is null ... */
              if (is_null($cell->getValue())) {
                  /* ... there is nothing. */
                  $row_data[$column_index] = '';
              }

              /* If this is a date ... */ else if (is_numeric($cell->getValue()) &&
                                  PHPExcel_Shared_Date::isDateTime($cell)) {
                  /* ... store a Parago date value. */
                  $dateobj = PHPExcel_Shared_Date::ExcelToPHPObject($cell->getvalue());
                  $row_data[$column_index] = $dateobj->format('d M Y');
              } else {
                  /* Store the data. */
                  $row_data[$column_index] = trim(( string ) $cell->getValue());
              }
                    }

          /* Return the row data. */
          return $row_data;
      }



      /**
       * Advance to the next row of the tabular data.
       * @return boolean true on success or false on error (end of worksheet / file is not an error)
       */
      public function gotoNextRow () {
          /* If the file isn't open, this is an error. */
          if (!$this->isFileOpen())
                  return $this->setError(self::ERROR_FILE_NOT_OPENED);

          /* Advance to the next row. */
          if ($this->_location_row >= 1) $this->_location_row++;

          /* Return success. */
          return true;
      }



      /**
       * Return if there a worksheet (no error or EOF)
       * @return boolean true if there is a current worksheet to read from.
       */
      public function hasCurrentWorksheet () {
          /* If the file isn't open, there isn't a row to read. */
          if (!$this->isFileOpen()) return false;

          /* If the worksheet isn't valid, there isn't a row to read. */
          if (!isset($this->_worksheet_info[$this->_location_worksheet]))
                  return false;

          /* Yes, there is a worksheet. */
          return true;
      }



      /**
       * Advance to the next worksheet of the tabular data.
       * @return boolean true on success or false on error (end of file is not an error)
       */
      public function gotoNextWorksheet () {
          /* If the file isn't open, this is an error. */
          if (!$this->isFileOpen())
                  return $this->setError(self::ERROR_FILE_NOT_OPENED);

          /* Reset the row location. */
          $this->_location_row = 1;

          /* Advance to the next worksheet. */
          if ($this->_location_worksheet >= 0)
                  $this->_location_worksheet++;

          /* Return success. */
          return true;
      }



      /**
       * Advance to the named worksheet of the tabular data.
       * @return boolean true on success or false on error (worksheet not found is not an error)
       */
      public function gotoNamedWorksheet ($worksheet_name) {
          /* If the file isn't open, this is an error. */
          if (!$this->isFileOpen())
                  return $this->setError(self::ERROR_FILE_NOT_OPENED);

          /* Reset the row location. */
          $this->_location_row = 1;

          /* If we have no worksheet information, ... */
          if (is_null($this->_worksheet_info)) {
              /* ... set it to something safe. */
              $this->_location_worksheet = -1;
              return true;
          }

          /* Otherwise, iterate over the worksheets ... */
          foreach ($this->_worksheet_info as $worksheet_index =>
                                  $worksheet_info)
                    {
              /* If this worksheet is the named one ... */
              if (isset($worksheet_info['worksheetName']) && ($worksheet_info['worksheetName'] ==
                                  $worksheet_name)) {
                  /* ... store the worksheet index and stop. */
                  $this->_location_worksheet = $worksheet_index;
                  return true;
              }
                    }

          /* Didn't find the worksheet, set it to something safe. */
          $this->_location_worksheet = -1;
          return true;
      }



      /**
       * Get the name of the current worksheet
       * @return string|boolean worksheet name or false on error
       */
      public function getCurrentWorksheetName () {
          /* If the file isn't open, this is an error. */
          if (!$this->isFileOpen())
                  return $this->setError(self::ERROR_FILE_NOT_OPENED);

          /* If the worksheet isn't valid, there isn't a row to read. */
          if (!isset($this->_worksheet_info[$this->_location_worksheet]))
                  return $this->setError(self::ERROR_END_OF_FILE);

          /* Return the current workspace name. */
          return $this->_worksheet_info[$this->_location_worksheet]['worksheetName'];
      }



      /**
       * Get the index of the current worksheet
       * @return integer|boolean worksheet index or false on error
       */
      public function getCurrentWorksheetIndex () {
          /* If the file isn't open, this is an error. */
          if (!$this->isFileOpen())
                  return $this->setError(self::ERROR_FILE_NOT_OPENED);

          /* If the worksheet isn't valid, there isn't a row to read. */
          if (!isset($this->_worksheet_info[$this->_location_worksheet]))
                  return $this->setError(self::ERROR_END_OF_FILE);

          /* Return the current workspace index. */
          return $this->_location_worksheet;
      }



      /**
       * Get the index of the current row
       * @return integer|boolean row index or false on error
       */
      public function getCurrentRowIndex () {
          /* If the file isn't open, this is an error. */
          if (!$this->isFileOpen())
                  return $this->setError(self::ERROR_FILE_NOT_OPENED);

          /* If the worksheet isn't valid, there isn't a row to read. */
          if (!isset($this->_worksheet_info[$this->_location_worksheet]))
                  return $this->setError(self::ERROR_END_OF_FILE);

          /* Get the worksheet info. */
          $worksheet_info = $this->_worksheet_info[$this->_location_worksheet];

          /* If the row isn't valid for the worksheet, there isn't one to read. */
          if (($this->_location_row < 1) || ($this->_location_row > $worksheet_info['totalRows']))
                  return $this->setError(self::ERROR_END_OF_WORKSHEET);

          /* Return the current row index. */
          return $this->_location_row;
      }



      /**
       * Return an opaque location object that can be used to move back to the
       * current location
       * @return mixed opaque (but serializable) location or false on error
       */
      public function getCurrentLocation () {
          /* Create the current location. */
          $identifier = $this->getLocationIdentifier();

          /* If we couldn't get the identifier, return an error. */
          if ($identifier === false) return false;

          /* Create the location and return it. */
          return array(
                'ident'     => $identifier,
                'row'       => $this->_location_row,
                'worksheet' => $this->_location_worksheet
          );
      }



      /**
       * Return to the location given by a location object
       * @param mixed the location to return to
       * @return boolean true on success or false on error (EOF is not an error)
       */
      public function gotoLocation ($location) {
          /* Validate that this location is for this file. If not return an error. */
          if (!$this->valiateLocation($location))
                  return $this->setError(self::ERROR_OTHER,
                                         'location not valid');

          /* Store the location. */
          $this->_location_row = $location['row'];
          $this->_location_worksheet = $location['worksheet'];

          /* Return success. */
          return true;
      }



      /**
       * Create an identifier for the file that is currently open
       * @return string|boolean the identifier or false on error.
       */
      private function getLocationIdentifier () {
          /* If the file isn't open, this is an error. */
          if (!$this->isFileOpen())
                  return $this->setError(self::ERROR_FILE_NOT_OPENED);

          /* Create a identifier string. */
          $identifier_str = $this->_file_path . "\0" . filesize($this->_file_path);

          /* Return the identifier. */
          return md5($identifier_str);
      }



      /**
       * Validate that this location is for this file.
       * @param mixed $location the location to valiate
       * @return boolean true if valid false if not.
       */
      private function validateLocation ($location) {
          /* If this isn't an array, this is invalid. */
          if (!is_array($location)) return false;

          /* If there isn't an identifier, row or worksheet, this is invalid. */
          if (!isset($location['ident']) || !is_string($location['ident']))
                  return false;
          if (!isset($location['row']) || !is_integer($location['row']))
                  return false;
          if (!isset($location['worksheet']) || !is_integer($location['worksheet']))
                  return false;

          /* Get our identifier. */
          $identifier = $this->getLocationIdentifier();

          /* If we don't have one, then this can't match. */
          if ($identifier === false) return false;

          /* If the identifier doesn't match, this is invalid. */
          if ($identifier != $location['ident']) return false;

          /* Return valid. */
          return true;
      }



      /**
       * Returns the total rows 
       * @return boolean|int
       */
      public function getTotalRows () {

          /* If the file isn't open, there isn't a row to read. */
          if (!$this->isFileOpen()) return false;
          if (( int ) $this->_location_worksheet !== -1) {
              return isset($this->_worksheet_info[$this->_location_worksheet]['totalRows']) ?
                                  $this->_worksheet_info[$this->_location_worksheet]['totalRows'] : 0;
          }
          /* If we got an exception then we we can't find a reader. */
          $this->setError(self::ERROR_OTHER,
                          'unable to get total rows from file reader');
          return 0;
      }





  }

  