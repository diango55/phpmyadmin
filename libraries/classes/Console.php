<?php
/**
 * Used to render the console of PMA's pages
 */
declare(strict_types=1);

namespace PhpMyAdmin;

use PhpMyAdmin\Bookmark;
use PhpMyAdmin\Relation;
use PhpMyAdmin\Template;

/**
 * Class used to output the console
 */
class Console
{
    /**
     * Whether to display anything
     *
     * @access private
     * @var bool
     */
    private $_isEnabled;

    /**
     * Whether we are servicing an ajax request.
     *
     * @access private
     * @var bool
     */
    private $_isAjax;

    /** @var Relation */
    private $relation;

    /** @var Template */
    public $template;

    /**
     * Creates a new class instance
     */
    public function __construct()
    {
        $this->_isEnabled = true;
        $this->relation = new Relation($GLOBALS['dbi']);
        $this->template = new Template();
    }

    /**
     * Set the ajax flag to indicate whether
     * we are servicing an ajax request
     *
     * @param bool $isAjax Whether we are servicing an ajax request
     *
     * @return void
     */
    public function setAjax(bool $isAjax): void
    {
        $this->_isAjax = $isAjax;
    }

    /**
     * Disables the rendering of the footer
     *
     * @return void
     */
    public function disable(): void
    {
        $this->_isEnabled = false;
    }

    /**
     * Renders the bookmark content
     *
     * @return string
     *
     * @access public
     */
    public static function getBookmarkContent(): string
    {
        $template = new Template();
        $cfgBookmark = Bookmark::getParams($GLOBALS['cfg']['Server']['user']);
        if ($cfgBookmark) {
            $bookmarks = Bookmark::getList(
                $GLOBALS['dbi'],
                $GLOBALS['cfg']['Server']['user']
            );
            $count_bookmarks = count($bookmarks);
            if ($count_bookmarks > 0) {
                $welcomeMessage = sprintf(
                    _ngettext(
                        'Showing %1$d bookmark (both private and shared)',
                        'Showing %1$d bookmarks (both private and shared)',
                        $count_bookmarks
                    ),
                    $count_bookmarks
                );
            } else {
                $welcomeMessage = __('No bookmarks');
            }
            return $template->render('console/bookmark_content', [
                'welcome_message' => $welcomeMessage,
                'bookmarks' => $bookmarks,
            ]);
        }
        return '';
    }

    /**
     * Returns the list of JS scripts required by console
     *
     * @return array list of scripts
     */
    public function getScripts(): array
    {
        return ['console.js'];
    }

    /**
     * Renders the console
     *
     * @return string
     *
     * @access public
     */
    public function getDisplay(): string
    {
        if (! $this->_isAjax && $this->_isEnabled) {
            $cfgBookmark = Bookmark::getParams(
                $GLOBALS['cfg']['Server']['user']
            );

            $image = Html\Generator::getImage('console', __('SQL Query Console'));
            $_sql_history = $this->relation->getHistory(
                $GLOBALS['cfg']['Server']['user']
            );
            $bookmarkContent = static::getBookmarkContent();

            return $this->template->render('console/display', [
                'cfg_bookmark' => $cfgBookmark,
                'image' => $image,
                'sql_history' => $_sql_history,
                'bookmark_content' => $bookmarkContent,
            ]);
        }
        return '';
    }
}
