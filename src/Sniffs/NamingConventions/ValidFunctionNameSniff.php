<?php

namespace Packaged\CodeStandards\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Standards\PEAR\Sniffs\NamingConventions\ValidFunctionNameSniff as OriginalValidFunctionNameSniff;
use PHP_CodeSniffer\Util\Common;

/**
 * PackagedCodeStandards_Sniffs_NamingConventions_ValidFunctionNameSniff.
 *
 * Ensures method names are correct depending on whether they are public
 * or private, and that functions are named correctly.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt
 *            BSD Licence
 * @version   Release: 1.4.3
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class ValidFunctionNameSniff extends OriginalValidFunctionNameSniff
{
  /**
   * Processes the tokens within the scope.
   *
   * @param File $phpcsFile                 The file being processed.
   * @param int  $stackPtr                  The position where this token was
   *                                        found.
   * @param int  $currScope                 The position of the current scope.
   *
   * @return void
   */
  protected function processTokenWithinScope(
    File $phpcsFile, $stackPtr, $currScope
  )
  {
    $methodName = $phpcsFile->getDeclarationName($stackPtr);
    if($methodName === null)
    {
      // Ignore closures.
      return;
    }

    $className = $phpcsFile->getDeclarationName($currScope);
    $errorData = [$className . '::' . $methodName];

    // Is this a magic method. i.e., is prefixed with "__" ?
    if(preg_match('|^__|', $methodName) !== 0)
    {
      $magicPart = strtolower(substr($methodName, 2));
      if(in_array($magicPart, $this->magicMethods) === false)
      {
        $error = 'Method name "%s" is invalid; only PHP magic methods should be prefixed with a double underscore';
        $phpcsFile->addError(
          $error,
          $stackPtr,
          'MethodDoubleUnderscore',
          $errorData
        );
      }

      return;
    }

    // PHP4 constructors are allowed to break our rules.
    if($methodName === $className)
    {
      return;
    }

    // PHP4 destructors are allowed to break our rules.
    if($methodName === '_' . $className)
    {
      return;
    }

    $methodProps = $phpcsFile->getMethodProperties($stackPtr);
    $isPublic = ($methodProps['scope'] === 'public') ? true : false;
    $scope = $methodProps['scope'];
    $scopeSpecified = $methodProps['scope_specified'];

    // If it's a private method, it must have an underscore on the front.
    if($isPublic === false && $methodName[0] !== '_')
    {
      $error = '%s method name "%s" must be prefixed with an underscore';
      $data = [
        ucfirst($scope),
        $errorData[0],
      ];
      $phpcsFile->addError($error, $stackPtr, 'PrivateNoUnderscore', $data);
      return;
    }

    // If it's not a private method, it must not have an underscore on the front.
    if($isPublic === true && $scopeSpecified === true && $methodName[0] === '_')
    {
      $error = '%s method name "%s" must not be prefixed with an underscore';
      $data = [
        ucfirst($scope),
        $errorData[0],
      ];
      $phpcsFile->addError($error, $stackPtr, 'PublicUnderscore', $data);
      return;
    }

    // If the scope was specified on the method, then the method must be
    // camel caps and an underscore should be checked for. If it wasn't
    // specified, treat it like a public method and remove the underscore
    // prefix if there is one because we cant determine if it is private or
    // public.
    $testMethodName = $methodName;
    if($scopeSpecified === false && $methodName[0] === '_')
    {
      $testMethodName = substr($methodName, 1);
    }

    if(Common::isCamelCaps($testMethodName, false, $isPublic, false) === false)
    {
      if($scopeSpecified === true)
      {
        $error = '%s method name "%s" is not in camel caps format';
        $data = [
          ucfirst($scope),
          $errorData[0],
        ];
        $phpcsFile->addError($error, $stackPtr, 'ScopeNotCamelCaps', $data);
      }
      else
      {
        $error = 'Method name "%s" is not in camel caps format';
        $phpcsFile->addError($error, $stackPtr, 'NotCamelCaps', $errorData);
      }

      return;
    }
  }

  protected function processTokenOutsideScope(File $phpcsFile, $stackPtr)
  {
  }
}
