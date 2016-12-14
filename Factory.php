<?php

  /*
   * Description of factory
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

  class PhpExcel_Factory {



      /**
       * Array of handler classes to check.
       * @var array
       */
      static private $handlers = array(
            'PhpExcel_Excel'
      );



      /**
       * Return if we can handle the supplied file. 
       * @param string $filename the file to check.
       */
      static public function canHandleFile ($filename) {
          /* Return if we can find a handler. */
          return (self::getHandler($filename) != false);
      }



      /**
       * Return the class name of the tablefile class to handle a file
       * @param string $filename the file to check.
       * @return string|boolean the class to use or false if none
       */
      static public function getHandler ($filename) {
          /* Iterate over the classes to check. */
          foreach (self::$handlers as $handler_name)
                    {
              /* Create a intance of the class. */
              $handler = new $handler_name();

              /* If the handler will handle the file ... */
              if ($handler->canHandleFile($filename)) {
                  /* ... return success. */
                  return $handler_name;
              }
                    }

          /* Otherwise, we can't handle the file. */
          return false;
      }





  }

  