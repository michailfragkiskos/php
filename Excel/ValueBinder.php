<?php

  /*
   * Description of ValueBinder
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

  class PhpExcel_Excel_ValueBinder
                    implements PHPExcel_Cell_IValueBinder {



      /**
       * Bind value to a cell 
       * @param PHPExcel_Cell $cell Cell to bind value to
       * @param mixed $value Value to bind in cell
       * @return boolean
       */
      public function bindValue (PHPExcel_Cell $cell, $value = null) {
          // sanitize UTF-8 strings
          if (is_string($value)) {
              $value = PHPExcel_Shared_String::SanitizeUTF8($value);
          }

          // Set value explicit
          $cell->setValueExplicit($value,
                                  self::dataTypeForValue($value));

          // return true
          return TRUE;
      }



      /**
       * DataType for value 
       * @param mixed $value
       * @return string
       */
      public static function dataTypeForValue ($value = null) {
          // Match the value against a few data types
          if (is_null($value)) {
              return PHPExcel_Cell_DataType::TYPE_NULL;
          } elseif ($value === '') {
              return PHPExcel_Cell_DataType::TYPE_STRING;
          } elseif ($value instanceof PHPExcel_RichText) {
              return PHPExcel_Cell_DataType::TYPE_INLINE;
          } elseif ($value{0} === '=' && strlen($value) > 1) {
              return PHPExcel_Cell_DataType::TYPE_FORMULA;
          } elseif (is_bool($value)) {
              return PHPExcel_Cell_DataType::TYPE_BOOL;
          } elseif (is_float($value) || is_int($value)) {
              return PHPExcel_Cell_DataType::TYPE_NUMERIC;
          } elseif (is_string($value) && array_key_exists($value,
                                                          PHPExcel_Cell_DataType::getErrorCodes())) {
              return PHPExcel_Cell_DataType::TYPE_ERROR;
          } else {
              return PHPExcel_Cell_DataType::TYPE_STRING;
          }
      }





  }

  