<?php
/**
 * Ensures that systems, asset types and libs are included before they are used.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer_MySource
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Ensures that systems and asset types are used if they are included.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer_MySource
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class MySource_Sniffs_Channels_UnusedSystemSniff implements PHP_CodeSniffer_Sniff
{

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_DOUBLE_COLON);

    }//end register()


    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in
     *                                        the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Check if this is a call to includeSystem or includeAsset.
        if (strtolower($tokens[($stackPtr + 1)]['content']) === 'includesystem') {
            $systemName = $phpcsFile->findNext(T_CONSTANT_ENCAPSED_STRING, ($stackPtr + 2));
            $systemName = trim($tokens[$systemName]['content'], " '");
        } else if (strtolower($tokens[($stackPtr + 1)]['content']) === 'includeasset') {
            $systemName  = $phpcsFile->findNext(T_CONSTANT_ENCAPSED_STRING, ($stackPtr + 2));
            $systemName  = trim($tokens[$systemName]['content'], " '");
            $systemName .= 'assettype';
        } else {
            return;
        }

        $systemName = strtolower($systemName);

        // Now check if this system is used anywhere in this scope.
        $level = $tokens[$stackPtr]['level'];
        for ($i = ($stackPtr + 1); $i < $phpcsFile->numTokens; $i++) {
            if ($tokens[$i]['level'] < $level) {
                // We have gone out of scope.
                break;
            }

            if ($tokens[$i]['code'] !== T_DOUBLE_COLON) {
                continue;
            }

            $usedName = strtolower($tokens[($i - 1)]['content']);
            if ($usedName === $systemName) {
                // The included system was used, so it is fine.
                return;
            }
        }

        // If we get to here, the system was not use.
        $error = "Included system \"$systemName\" is never used";
        $phpcsFile->addError($error, $stackPtr);

    }//end process()


}//end class

?>
