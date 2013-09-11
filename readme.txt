=== teachPress ===
Contributors: Michael Winkler
Tags: management, publications, enrollments, teachpress, education, course management, BibTeX, bibliography
License: GPLv2 or later
Requires at least: 3.3
Tested up to: 3.6
Stable tag: 4.2.2

With this plugin you can easy manage courses, enrollments and publications.

== Description ==
The plugin unites a course management system (with enrollments) and a BibTeX compatible publication management. teachPress is optimized for the needs of professorships and research groups. You can use it with WordPress 3.3.0 or higher.

For more information see [here](http://www.mtrv.wordpress.com/teachpress/).

= Features: =
* BibTeX compatible multi user publication management
* BibTeX import for publications
* BibTeX and RTF export for publications
* RSS feed for publications
* Course management with an integrated enrollment system
* XLS/CSV export for course lists
* Widget for displaying books in a sidebar
* Many shortcodes for an easy using of publication lists, publication searches, enrollments and course overviews

= Supported Languages =
* English 
* German
* Italian (o)
* Portuguese (Brazil) (o)
* Slovak (o)
* Spanish (o)

(o) Outdated language files

= Disclaimer =  
Use at your own risk. No warranty expressed or implied is provided.  

== Credits ==

Copyright 2008-2013 by Michael Winkler

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

= Licence information of external resources =
* Graphics for mimetypes by Oxygen Icons 4.3.1 http://www.oxygen-icons.org/ (Licence: LPGL)
* view-refresh-3.png by GNOME Icon Theme 2.26.0 http://art.gnome.org (License: GPLv2)
* bibtexParse by Mark Grimshaw & Guillaume Gardey (Licence: GPL)

= Thanks =
I would like to thank the team of [CBIS, Chemnitz University of Technology](http://www.tu-chemnitz.de/wirtschaft/wi2/wp/en/) for the support and the collaboration during the last years.

= Translators who did a great job in translating the plugin into other languages. Thank you! =
* [Jozef Dobos] (http://xn--dobo-j6a.eu/) (Slovak)
* Elisabetta Mancini (Italian)
* Aurelio Pons (Spanish)
* Marcus Tavares (Portuguese-Brazil)

== Installation ==

1. Download the plugin.
2. Extract all the files. 
3. Upload everything (keeping the directory structure) to your plugins directory.
4. Activate the plugin through the 'plugins' menu in WordPress.

**For updates:**

1. Download the plugin.
2. Delete all files in the 'plugins/teachpress/' directory.
3. Upload all files to the 'plugins/teachpress/' directory.
4. Go in the backend to Courses->Settings and click on "Update to ....".

== Screenshots ==
1. Add publication menu
2. Add course menu
3. Example for a publication list created with [tpcloud]
 

== Frequently Asked Questions ==

= How I can use the shortcodes? =
[See teachPress shortcode reference](http://mtrv.wordpress.com/teachpress/shortcode-reference/)

= How can I add a course list in a page or post? = 
If you writing a post or page use the following tag: [tpcourselist]

= How can I add the enrollment system in my blog? =
Create a new page or post and use the following tag: [tpenrollments]

= How can I add longer course desciptions? =
You can write long course desciptions, as normal WordPress pages. The main function of teachPress is only to connect the static page with a course overview and an enrollment system. For this you can specify a related page for your course.

= How can I display images in publication lists? =
An example: [tplist user="0" image="left" image_size="70"]. Important: You must specify both image parameters.

= How can I deactivate parts of the plugin? =
If you want to use only one part of the plugin, so write the following in the wp-config.php of your WordPress installation
For deactivating the course system:  
define ('TP_COURSE_SYSTEM','disable');  
For deactivating the publication system:  
define ('TP_PUBLICATION_SYSTEM','disable');  

= I see only error messages if I use the RSS-Feed for publications or the xls/csv export for enrollments. What's wrong? =
If you save plugins outside the normal path (/wp-content/plugins/), the plugin can't load required WordPress files in some cases. Solution: Change the path in the following plugin files: export.php (line 9) / feed.php (line 7).

== Changelog ==
= 4.2.2 - (11.09.2013) =
* Bugfix: Fixed a bug which prevent adding of terms, course types and courses of studies
= 4.2.1 - (11.09.2013) =
* New: [tpcloud], [tplist], [tpsearch]: Style option "std_num" added
* New: [tpcloud], [tplist]: Style option "std_num_desc" added
* Changed: Auto wordwrap for abstracts disabled
* Bugfix: Fixed a problem with the import of BibTeX data which are enclosed with double quotes
= 4.2.0 - (31.08.2013) =
* New: Shortcodes [tplinks], [tpbibtex], [tpabstract] added
* New: More filters for publications on admin screens
* New: Export for .bib files added
* New: Import for .bib and .txt files added
* New: Import option for forcing updates of existing publications added
* New: Simple generator for bibtex keys added
* New: Auto correction for spaces in bibtex keys added
* New: Support for some html expressions (b,i,u,sup,sub,u,ul,li) and the conversion to their latex equivalents in abtracts added.
* New: Screen options for some admin screens added
* New: [tplist], [tpcloud]: Style option "numbered_desc" added
* New: [tpcloud]: New parameter "hide_tags" added
* New: [tpsearch]: New parameters "user" and "tag" added
* New: [tpsingle]: New parameters "image", "image_size" and "link" added
* New: Parameters "user" and "exclude" for get_tp_tags() added
* New: Parameter "exclude" for get_tp_tag_cloud() added
* Bugfix: Fixed a problem with the return of get_tp_publications() if the function was used in count mode and publications were filtered by year
* Bugfix: Editorials were not identified correctly
* Bugfix: Tags were not editable (tag management page)
* Bugfix: [tpcloud]: A list in "numbered" style started with 0
* Bugfix: [tpcloud]: "Exclude" parameter was ignored
* Bugfix: [tpsearch]: Impossible to use the search if WordPress uses no permalink structure
* Bugfix: [tplist]: Useless default values for "user" and "tag" removed
= 4.1.1 - (06.07.2013) =
* Bugfix: Fixed an division through zero problem in teachpress_addpublications_page()
* Bugfix: Fixed an improper presentation of meta information in some cases if the publication type is presentation
* Bugfix: [tpenrollments]: Prevent execution of tp_add_signup() and tp_delete_signup_student() if there was no course selected
* Bugfix: Fixed embedding of scripts and images for SSL-Sessions 
= 4.1.0 - (13.06.2013) =
* New: [tplist]: Optional pagination added
* New: [tplist]: New parameters "pagination" and "entries_per_page" added
* New: [tplist]: New headline option added (sort by year and type)
* New: Publication type "periodical" added
* New: Field "issuetitle" added for publications with the type "periodical"
* Bugfix: [tpcloud]: Changing of the "order" parameter was not working
* Bugfix: Fixed a bug which prevents adding of publications
* Bugfix: Unable to delete all databases with tp_uninstall()
= 4.0.5 - (05.05.2013) =
* Bugfix: [tpenrollments]: Fixed possible destroying of templates, [reported in WP Support Forum](http://wordpress.org/support/topic/bug-registration-form-in-tpenrollments-destroyes-design)
* Bugfix: [tpenrollments]: Not fillable input fields in the registration form, [reported in WP Support Forum](http://wordpress.org/support/topic/bug-registration-form-in-tpenrollments-destroyes-design)
* Bugfix: [tpenrollments]: User registration doesn't work, [reported in WP Support Forum](http://wordpress.org/support/topic/bug-formula-tp_registration_form-do-not-work-for-registration)
* Bugfix: Fixed a wrong SQL-Request if tags are not exists in get_tp_tag_cloud(), [reported in WP Support Forum](http://wordpress.org/support/topic/bug-see-some-php-code-under-the-add-publication-page)
* Bugfix: Fixed a wrong call of objects under some conditions in tp_registration_form()
* Bugfix: Fixed an untimely loading of data under some conditions in teachpress_students_page()
* Bugfix: Publication import: Try to fix a problem with line breaks within keywords, [reported here](http://mtrv.wordpress.com/teachpress/comment-page-4/#comment-2123)
= 4.0.4 - (15.03.2013) =
* Bugfix: Fixed a bug which prevent deleting terms, courses of studies and course types
= 4.0.3 - (13.03.2013) =
* New: [tpsingle]: New parameter "key" added
* Bugfix: [tpcourselist]: Sub courses were displayed in a wrong way
* Bugfix: [tpcloud, tplist, tpsearch, tpsingle]: Fixed the handling of "In:" strings for publication meta rows
* Bugfix: Publications could not be deleted with the first try
* Bugfix: Fixed a bug with missing keywords/tags in BibTeX based publication feeds
= 4.0.2 - (07.03.2013) =
* Bugfix: Try to fix a problem with unvisible publications
* Bugfix: Fixed a problem with a possible division through in add_publication.php
= 4.0.1 - (28.02.2013) =
* Bugfix: [tpcloud]: Tag cloud generation fixed if parameter user is enabled
* Bugfix: [tpcloud, tplist, tpsearch]: Publication list generation fixed and improved
= 4.0.0 - (27.02.2013) =
* New: Publication types "online" and "collection" added
* New: Field "urldate" added for publications with the type "online"
* New: Shortcode [tpsearch](http://mtrv.wordpress.com/teachpress/shortcode-reference/tpsearch/) added
* New: Numbered publication lists are available
* New: Single course overview redesigned
* New: Enrollments can be moved to releated courses
* New: Sort options for enrollments added
* New: Include parameter for tplist added
* New: Support for network installations added
* New: Automatic permalink detection added
* New: teachPress core API added
* Changed: Parsing of publication meta information for all shortcodes
* Changed: BibTeX import improved
* Changed: RSS feed generation improved
* Changed: Publication search improved
* Bugfix: [tpcloud, tplist, tpsingle]: Some bugs with the publication meta row output fixed
* Bugfix: Wrong flag (selected) was sometimes returned by get_tp_wp_pages() 
* Bugfix: Deprecated function eregi was replaced
* Bugfix: Optional "type" field was declared as "techtype"
* Bugfix: Fixed a bug which arised if the function "Show as BibTeX entry" was used without selecting publications before
* Killed: Menu "Add manually" replaced
* Killed: [tpcloud]: Support for "id" parameter replaced. Please use "user" instead
= 3.1.10 - (28.10.2012) =
* Bugfix: Year was not displayed in a string which was generated by [tpsingle]
= 3.1.9 - (23.10.2012) =
* Bugfix: Fixed a bug with the year filter of tplist
* Bugfix: Fixed a bug in bibtex implementation --> name is now a title
= 3.1.8 - (07.10.2012) =
* New: Portuguese (Brazil) translation added
* Bugfix: Fixed a small bug in show_courses.php (variable declaration)
= 3.1.7 - (18.09.2012) =
* New: Allow articles as linked source
* New: Introduce some functions of the planned teachpress 4 API (i.e. tp_is_user_subscribed)
* New: Don't load admin functions in the frontend 
= 3.1.6 - (03.08.2012) =
* Changed: Update for slovak translation
= 3.1.5 - (01.08.2012) =
* Changed: Update for slovak translation
* Changed: Update all .po language files
= 3.1.4 - (25.07.2012) =
* Bugfix: Fixed a bug at adding/changing of courses (was introduced with 3.1.3)
= 3.1.3 - (25.07.2012) =
* Bugfix: Fixed a silent overbooking of courses
* Bugfix: Fixed the complete handling of free places to reduce possible collisions of database queries during the enrollment process
= 3.1.2 - (14.06.2012) =
* New: [tpenrollments, tpcourselist]: New parameter "term" for selecting the term you want to show
= 3.1.1 - (05.04.2012) =
* Bugfix: Missing statement for full courses in the enrollment system
* Bugfix: The url field of teachpress_pub table was not changed from varchar to text
= 3.1.0 - (24.03.2012) =
* New: [tplist, tpcloud]: New parameter "exclude" for excluding publications from the lists
* New: [tplist, tpcloud]: New parameter "link_style"
* New: [tplist, tpcloud]: Abstracts will be displayed directly in publication lists
* New: [tplist]: Abstracts and BibTeX entries will be displayed
* New: [tplist]: Expand type parameter for more than one publication type
* New: Sending emails as a separate copy to the email author (optional)
* New: Adding more than one website/file to a publication
* New: Some small UI improvements
* New: Slovak translation added
* Changed: URL field for publications changed from varchar to text
* Changed: Using INNODB as default database engine
* Changed: Using transactions for subscribing/unsubscribing users
* Changed: Names of some CSS style classes in publication lists
* Bugfix: BibTeX import ignores date field
* Bugfix: BibTeX export could not display "edition" field
* Bugfix: Colspan was not set in publication tables
* Bugfix: Highlighting of the saved date in datepicker doesn't work
* Bugfix: Students were not correctly deleted
* Bugfix: Fixed a bug in displaying publications: year was not displayed for booklets
* Bugfix: Fixed a bug which prevent an installation under WordPress Multisite
* Bugfix: Fixed a bug which prevent adding of publications
* Killed: Log system, teachpress_log table
= 3.0.1 - (06.01.2012) =
* Bugfix: Fixed a bug which prevent sending of e-mail notifications
= 3.0.0 - (05.01.2012) =
* New: [tpcourselist]: New option for hiding the headline
* New: [tpcourselist]: New option for displaying a simple text under the headline
* New: [tpcloud, tplist]: New option for listing publications after type, year (Set headline="2" for this)
* New: [tpcloud]: New parameter "headline" is available
* New: Tags (Keywords) will be displayed in BibTeX entries
* New: E-mail notifications for the enrollment system (if users are signed up for a course or for a waitinglist of a course)
* New: Writing e-mails directly from the admin menu
* New: File export for publications added: .txt(BibTeX) and .rtf format
* New: Datepicker translations for Spanish and Italian added
* New: Plural forms for publication types added
* Changed: Use add_help_tab instead an own help tab on teachpress admin screens
* Changed: Use the integrated jQuery library from WordPress (i.e. datepicker) instead of an own
* Changed: Use the style of button-primary / button-secondary from WP instead of an own copy
* Changed: Line breaks in course comments will be displayed
* Changed: [tpcloud]: "id"-parameter renamed to "user"
* Changed: [tpdate]: Hide invisible child courses
* Changed: [tpcloud, tplist]: If there is no URL given for a publication, teachPress tests if there is an related page instead
* Bugfix: Using of undefined constants
* Bugfix: Using of undefined variables
* Bugfix: Publication title was not displayed in BibTeX strings
* Bugfix: Deprecated call of load_plugin_textdomain
* Bugfix: Installation of the tables teachpress_relation, teachpress_signup failed
* Bugfix: [tpcloud]: Unable to reset the publication type filter 
= 2.3.3 - (06.07.2011) =
* New: WordPress Media Importer is now usable for the URL field of a publication
* Changed: The parameters.php isn't longer required
* Bugfix: Fixed some CSS bugs in conjunction with WordPress 3.2
= 2.3.1/2.3.2 - (07.06.2011) =
* Bugfix: Fixed two small errors
= 2.3.0 - (06.06.2011) =
* New: teachPress is now compatible with WordPress 3.2
* New: An option for selecting all checkboxes is now available in some admin menus
* New: The deactivation of the course/publication system is possible
* Changed: Visibility handling for courses
* Bugfix: Fixed a bug in the shortcode [tpcourselist]: With activated permalink structure it was in some cases for users not possible to select an other semester
* Bugfix: Fixed a bug in the page menu: Wrong page number calculation under determined conditions
* Bugfix: Fixed a bug in the enrollments system: If the course and the sub-course name were the same, the course type was displayed instead of the sub-course type
* Bugfix: Fixed a bug in the enrollments system: If there is no related page given, the course name isn't longer a link
* Bugfix: Fixed the bibtex import for several special chars
= 2.2.0 - (17.04.2011) =
* New: "order" option for the shortcodes [tplist], [tpcloud]
* New: "type" option for the shortcodes [tplist], [tpcloud]
* New: Improved filter system for the backend publication menu
* New: teachPress can now manage the number of free places of a course automatically.
* New: Improved visibility options for courses
* Changed: Pagemenus have now the WordPress 3.0 Design
* Changed: Backend function have their own file: core/admin.php
* Bugfix: Fixed a redirect bug in the backend publication menu
* Bugfix: Fixed a small CSS bug in the frontend courselist, shortcode: [tpcourselist]
* Bugfix: The year 0000 is no longer visible in the year filter of [tpcloud] - 0000 stands for no date given - publications with no date are furthermore visible at the end of the publication list
* Killed: Detailed parameter description for shortcodes in the help sections --> moved to docs/shortcodes.html
* Killed: Language files for en_US (because it's already the basic plugin langauge)
= 2.1.2 - (15.03.2011) =
* New: If you want it, you can now use custom post types instead of pages for the related page links
* Info: WordPress 2.8 is not longer supported
= 2.1.1 - (10.03.2011) =
* Bugfix: Fixed a bug in the publication overview (backend): The tags were not displayed
* Bugfix: Fixed a bug in the updater which set the field type for birthday in the table teachpress_stud to varchar and not to date
* Bugfix: Fixed a bug when student data were edited via backend: Data were lost
* Bugfix: Fixed a bug which prevent an direct edit after adding a course/publication
= 2.1.0 - (08.03.2011) =
* New: Strict Subscribing
* New: Improved Admin menu
* Bugfix: Fixed a bug with a wrong redirect after the user cancelled the deleting of students
= 2.0.14 - (24.02.2011) =
* New: New style option for [tpcloud], [tplist]
* Bugfix: Fixed a bug in teachPress books widget - the name of a book is no longer a html-element name
* Bugfix: The Room is now vissible again in single course overviews (Bug was introduced with version 2.0.10)
= 2.0.13 - (20.02.2011) =
* New: Spanish translation added
= 2.0.12 - (01.02.2011) =
* New: Improved tag menu
* New: Improved students menu
* New: New style options for editor names - available for the shortcodes [tpcloud], [tplist], [tpsingle]
* New: New style options for author names - available for the shortcodes [tpsingle]
* Bugfix: Waiting lists are now sorted by registration date and not longer by user name
* Bugfix: Fixed a bug in single course menu: If there is no enrollment, enrollment details are not longer visible
* Bugfix: Some shortcode parameters are now more secure
* Bugfix: Fixed a bug which insert wrong links to the publication feeds in the settings menu
* Bugfix: Fixed some bugs with slashes with was not stripped for the final displaying. It's fixed for the xls/csv-export, the RSS parser and in some menus
* Bugfix: Fixed some bugs in xls/csv export
= 2.0.11 - (31.01.2011) =
* New: New style options for author names - available for the shortcodes [tpcloud], [tplist]
= 2.0.10 - (23.01.2011) = 
* New: Highlighting of child courses in the course overview 
* Bugfix: Fixed a html bug in show_single_course.php 
* Bugfix: Fixed the sort of participants if the registration number is disabled 
* Bugfix: Fixed a bug with the table of participants if the registration number is disabled
= 2.0.9 - (30.11.2010) =
* New: Better style permissions for all shortcodes. Some nasty hard coded CSS code is removed.
* Changed: Style for tpenrollments, tpdate and tpcloud shortcodes
* Changed: Italian translation updated
= 2.0.8 - (27.11.2010) =
* New: Support for WordPress 3.1
* New: Basic italian translation added
= 2.0.7 - (18.11.2010) =
* Bugfix: Fixed a bug with the charset in the xls-export - teachpress uses now utf8_encode()
* Bugfix: Fixed a bug in the bibtex output of articles
* Bugfix: Fixed some small GUI-bugs
* Bugfix: Fixed style of some buttons
= 2.0.6 - (24.10.2010) =
* Changed: Type of the registration timestamp has changed from date to datetime
* Bugfix: Fixed a security vulnerability (sql injection) which was opened with a fix in version 2.0.5
* Bugfix: teachPress uses now the right local server time and not longer greenwich time
= 2.0.5 - (11.10.2010) =
* New: Now you can set the time (hour, minute) for the start/end of a enrollment period
* Bugfix: Fixed a bug which displayed a wrong message after adding a student
* Bugfix: Fixed a bug which prevented the manual adding of students
* Bugfix: Some small user interface improvements
* Bugfix: Fixed some bugs in the english translation
= 2.0.4 - (02.10.2010) =
* Bugfix: Fixed a bug which prevented the installation of the teachpress_pub table
= 2.0.3 - (27.09.2010) = 
* Bugfix: Fixed a bug with one login mode (integrated)
= 2.0.2 - (27.09.2010) =
* Changed: New author and plugin website
* Bugfix: The year was not displaying for articles
* Bugfix: Images in the publication lists were not scaled
* Bugfix: Child courses are now visible, if their parent was deleted
* Bugfix: Fixed bugs in the admin course overview
* Bugfix: Course type was not selected when an user edit a course
* Bugfix: Fixed a variable declaration in get_tp_publication_type_options()
= 2.0.1 - (20.09.2010) =
* New: Introduce an option for deselecting the default teachPress frontend style
* Changed: Some small improvements for publication lists
* Bugfix: Fixed german translation for proceedings and inproceedings
* Bugfix: BibTeX-Key was not displaying in the frontend
= 2.0.0 - (18.09.2010) =
* New: BibTeX support (bibtex export, more data fields, more publication types)
* New: Shortcode "tpsingle" for displaying single publications
* New: Shortcode "tpcourselist" for displaying a course list
* New: Shortcode "tpenrollments" for displaying the enrollement system
* New: Shortcode "tppost" for displaying parts of a post only for registered students
* New: Images for courses
* Changed: Style of single publications generated with [tpsingle]
* Changed: Bibtex export now discerns isbn from issn
* Changed: Shortcode "tpcloud": It's now possible to deactivate the html anchor
* Changed: Redesigned user interface
* Changed: Number of chars for a semester name (from 10 to 100)
* Changed: Database and directory structure
* Changed: Some small improvements for publication lists
* Bugfix: Fixed bugs in the overview of students
* Bugfix: Fixed problems with the user data field selection for registration forms
* Bugfix: It's now possible to add images directly from the WordPress Media Library
* Bugfix: Fixed a bug with the email column in the course lists
* Bugfix: Fixed a bug in xls export: The parent course name is now displaying
* Killed: own database functions tp_var, tp_query, tp_results
* Bugfix: Fixed some bugs with the pagination in the students and the publication overview
* Bugfix: Delete the bugfix in tpdate shortcode from version 2.0.b3, because the bug was the bugfix
= 1.0.0 - (31.05.2010) =
* New: It is possible to deactivate some fields for user data
* New: New registration mode available
* New: Function for uninstalling teachPress
* Changed: Some small improvement for attendance lists
* Changed: Settings
* Changed: Design for enrollment system
* Changed: Calendar: from jscalendar to datepicker (jquery-plugin)
* Changed: Directory structure
* Bugfix: Fixed bugs with utf8 chars
= 0.85.1 =
* New: RSS-Feed script for publications
* Bugfix: Fixed bug in the "copy course" function
* Bugfix: Fixed bug in the "add students manually" function
= 0.85.0 =
* New: Displaying images in publication lists
* New: Larger edit field for course comments
* Bugfix: Size of visible images in the publication edit menu is limited
* Bufgix: Some function names now more unique
* Bugfix: Fix some security vulnerabilities
= 0.80.2 =
* Bugfix: Fixed different bugs, which originated with the file merging in the publication management in version 0.80.0
= 0.80.1 =
* Bugfix: Fixed a bug when adding a publication
= 0.80.0 =
* New: Capabilities for backend access control
* New: Possible to prevent sign outs for registrations 
* Changed: Style of frontend course overview
* Changed: Central definition of publication types in the source code 
* Changed: Select fields reworked
* Changed: Translation for publication types
* Changed: Put all javascript functions to standard.js
* Changed: Put the teachPress settings page from the courses menu to the Wordpress settings menu
* Bugfix: Fixed displaying child courses in display.php 
* Bugfix: Cleaned backend CSS and more CSS3 compatibility
* Bugfix: Fixed access bug for students.php
* Bugfix: Fixed updater
= 0.40.0 =
* New: teachPress books widget
* New: Add images to your publications
* New: Related pages for publications
* New: Related pages for courses
* New: ISSN field for publications
* Changed: Many small changes in the enrollment form (now display.php)
* Changed: Smarter script loading
* Bugfix: Fixed a bug when you add a student manually
* Bugfix: Fixed bug in the sort order of terms
* Bugfix: Fixed charset and collation for teachpress tables
* Bugfix: Fixed bug when parent and child course have the same name
* Killed: URL field for courses
= 0.32.0 = 
* Changed: Design for course overview
* Changed: Default language changed from german to english
* Bugfix: Fixed a bug when student unsubscribes from a course (Thanks to Jean T. )
* Bugfix: Fixed a bug in the course overview (frontend)
= 0.30.2 =
* Fixed a small problem with the version name
= 0.30.0 =
* New: Copy function for courses
* New: Simple CSV-export for enrollments
* New: Free selectable names for child courses
* New: More parameters for the [tpcloud] shortcode
* New: Using wpdb->prefix for database names
* Changed: Order of courses in the backend overview
* Changed: Structure of registration form
* Changed: Tag-Cloud creation
* Changed: Course search
* Bugfix: Fixed a bug in 'add courses' form
* Bugfix: Fixed a bug by using students search
* Bugfix: Fixed a bug in get_tp_message()
* Killed: XML-export for enrollments