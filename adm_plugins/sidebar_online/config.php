<?php
/**
 ***********************************************************************************************
 * Configuration file for Admidio plugin Sidebar-Online
 *
 * @copyright 2004-2018 The Admidio Team
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 ***********************************************************************************************
 */

// Time in minutes in which the users are still active (Default = 10)
$plg_time_online = 10;

// Should visitors (users who are not logged in) also be displayed
// 0 = only logged in members will be shown
// 1 = (Default) Number of visitors is listed
$plg_show_visitors = 1;

// Should logged in members be displayed to visitors (users who are not logged in)
// 0 = logged in members are not displayed
// 1 = logged in members are displayed
// 2 = (Default) only number of logged in members is displayed
$plg_show_members = 2;

// The own login should be displayed
// 0 = the own login (also logged out) will not be displayed
// 1 = (Default) the own login (also logged out) will be displayed
$plg_show_self = 1;

// Display of user names with each other or side by side
// 0 = (Default) List user names (1 name per line)
// 1 = List user names side by side
$plg_show_users_side_by_side = 0;

// Name of a CSS class for links
// Only necessary, if the links should get a different look
$plg_link_class = '';

// Specification of the target in which the contents of the links are to be opened
// You can insert specified values of the html target attribut
$plg_link_target = '_self';

// Should the header of the plugin be displayed
// 1 = (Default) Headline is displayed
// 0 = Headline is not displayed
$plg_show_headline = 1;
