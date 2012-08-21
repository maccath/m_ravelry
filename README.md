Macca's Ravelry v1.0 - Habari Plugin

Hooks into (pun intended) the Ravelry (http://ravelry.com) API to display progress bars for knitting and crochet 
projects.

Works on Habari 0.7.x - would hopefully work on Habari 0.8.x but UNTESTED. May need to add an echo to
block.m_ravelry.php - check the upgrade instructions here:
    http://wiki.habariproject.org/en/Upgrading#Upgrading_from_Version_0.7.x_to_Version_0.8

Usage instructions:
  * Activate and configure the plugin with your username and API key (find yours at http://www.ravelry.com/help/api)
  * Either edit your theme and add Macca's Ravelry blocks, or add the following anywhere in your theme to use the
    default configuration options:
        <?php $theme->m_ravelry(); ?>
  * Edit the HTML in m_ravelry.php and/or the CSS in m_ravelry.css to customise the appearance
  * Replace images/placeholder.jpg with your own placeholder image if you so desire