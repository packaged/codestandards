<?php

namespace Packaged\CodeStandards\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * @author gareth.evans
 */
class MultipleBlankLineSniff implements Sniff
{
  public function register()
  {
    return [T_WHITESPACE];
  }

  public function process(File $phpcsFile, $stackPtr)
  {
    $tokens = $phpcsFile->getTokens();
    $blankLines = 0;
    $previousStackPtr = $stackPtr - 1;
    $eolCharLen = strlen($phpcsFile->eolChar);
    $errorSet = false;

    do
    {
      $startChar = substr($tokens[$stackPtr]['content'], 0, $eolCharLen);
      if($startChar === $phpcsFile->eolChar)
      {
        $blankLines++;
      }
      else if($tokens[$stackPtr]['type'] !== "T_WHITESPACE")
      {
        break;
      }

      if($blankLines > 2 && !$errorSet)
      {
        $error = 'File must not contain multiple blank lines';
        $phpcsFile->addError($error, $previousStackPtr, 'MultipleBlankLines');
        $errorSet = true;
      }

      $stackPtr++;
    }
    while(isset($tokens[$stackPtr]));
  }
}
