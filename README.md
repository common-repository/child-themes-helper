# Child Themes Helper plugin Version 1.3.2
The Child Themes Helper plugin was developed to solve a problem that I was struggling with: the copying of files from a template theme to a child theme. It was almost never easy.
1. Copy the template theme file down to my local computer via FTP. That was easy enough.
1. Create the matching directory structure in my child theme. That was a bit more time-consuming. Invariably I would create a folder wrong and wouldn't notice until I tried to see my changes. I would reload the page and it would look the same. I would spend a ton of wasted time trying to figure out why my changes did not show up. Then I'd see it. A typo in a folder name. A missed subfolder in the tree. Or some other bonehead error.
1. Copy the file I downloaded in step 1 to the newly created path in the child theme.
1. Finally, I would start editing the file and look forward to seeing my changes when I refresh the browser.

There has to be a better way. I looked around and didn't really find one. So, being a programmer at heart, I wrote one. In fact, I wrote *THIS* one.

## Copy Files between Themes
The Child Themes Helper plugin solves the above problem with a nice elegant solution. The files in the child theme and the files in the template theme are displayed on the screen using a pair of CSS Grid boxes (child theme is listed on the left and template theme is listed on the right). The developer only needs to click on the file from the template theme and viola! the file is immediately copied to the child theme and all of the subfolders in the path are perfectly duplicated in the child theme.

But what good would a plugin be, which let you easily copy files from the template theme to the child theme, if you couldn't delete those copied files when the need arose. Well, the Child Theme Helper plugin lets you do just that. Simply click on any file from the child theme file list and poof, the file is removed from the child theme.

But wait. Isn't that an awful lot of unchecked power? Yes it is. Before a file is copied from the template theme to the child theme, or removed from the child theme, the Child Theme Helper plugin will verify that the file in the child theme hasn't been modified. If it has been modified (i.e., it's not identical to the same file in the template theme), then instead of that instant copy, you'll get a popup message telling you that the files are different, warning you that you will lose your changes, and asking you if you want to continue and overwrite (copying from the template to the child) or delete (removing from the child) the child theme file. You still have the power to delete or overwrite your modified file, but you have to take the extra step and verify that you really want to proceed.

Further, the Child Theme Helper plugin refuses any and all requests to overwrite or remove the style.css and/or the functions.php file. I don't want to be blamed because you destroyed your active theme and thus your website because you used the Child Theme Helper plugin to destroy your theme. So, you cannot delete or overwrite your style.css or functions.php files. If you really must do that, I suggest that you use your favorite FTP client.

The Child Theme Helper plugin only manipulates the child theme files. The template theme is never touched. A new feature that is currently planned, but not yet implemented, is the ability to edit the files for a quick peek or a quick modification. Once that feature has been completed, the developer will be able to open the template theme files, but they will not be able to save any modifications. Only changes to the child theme files may be saved.

## Edit Child Themes Files
Starting with version 1.3.2, you can edit child theme files directly, and save your changes. You can open parent theme files in an editor, but parent theme files are read-only. If you make changes to a parent theme file, you will not be able to save theme.

## Create a New Child Theme
The Child Theme Helper plugin was intended for one purpose: to copy files to the child theme. But what should it do when the currently active theme isn't a child theme? My first thought was to disable it somehow. But then I thought, why not give the developer the opportunity to create a new child theme.

When the currently active theme is not a child theme, the Child Theme Helper plugin will prompt you (on it's dashboard page) to create a new child theme for any theme that you have installed, whether it's the active theme or not, with the exception of other child themes. WordPress doesn't allow a child theme to use another child theme as its template theme. Once the new child theme has been created, you are redirected to the website's Themes page on the dashboard. The new child theme __IS NOT activated__ by the Child Theme Helper plugin, although it __is ready__ to be activated.

When the Child Theme Helper plugin creates a new theme, it does three things:
1. __style.css__ is created with the typical WordPress header filled out as indicated from the creation form. Once completed, the theme will display on the list of available themes installed on your website.
1. __functions.php__ is created that loads the template theme style.css first, and then loads the child theme style.css second using *wp_enqueue_style*.
1. __screenshot.png__ is created to help you quickly and easily recognize your newly created child theme in your list of themes. When the page reloads, you are redirected to the themes page on your WordPress Dashboard. The Child Theme Helper __plugin DOES NOT activate__ the newly created child theme. It is ready to be activate and you may do so on the WordPress Dashboard's Themes page. Once the child theme is active, the next time you load the Child Themes Helper page from its Dashboard menu, you will see the list of files for the child theme and the template theme.
