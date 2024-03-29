# WordPress-phpBB bridge
A plugin for [WordPress](https://wordpress.org) that was used on [nam-guild.com](https://nam-guild.com) to connect WordPress (5.x) and phpBB (3.0.14). It also included a sidebar widget for logging onto phpBB. The widget was based upon [Mike Jolley](http://blue-anvil.com)'s plugin named [Sidebar Login](http://wordpress.org/extend/plugins/sidebar-login/).

Originally created in 2010. It was my first ever plugin, so the directory name is aptly named "test". On 19th of August 2019 this repository was created.

For those that want to try it out, the following write up is based upon my notes. **NB!** May be flawed since I've only re-tried this once since 2010. Proceed at your own peril! 

Anyway, here we go:
1. Have access to a [Founder](https://wiki.phpbb.com/Founder) user on your phpBB so that you gain administrator capabilities automatically when everything is in place.
2. Use InnoDB as storage engine. (I exported, and then edited the exported .sql file so that the tables would be re-inserted into the database using InnoDB).
3. Best if done on a fresh WordPress. (I did it on a existing one).
4. Should activate the plugin first (I cheated and did this in the database the first time)
   1. Go to **Settings** -> **WordPress-phpBB bridge** for settings.
   2. Set the correct URL and path to phpBB before proceeding. 
   3. Activate the Bridge
5. Re-create the table *wp_users* as a [view](https://mariadb.com/kb/en/library/create-view/) based upon the table *phpbb_users*: 
```SQL
ALTER TABLE wp_users RENAME TO wp_users_bak;

CREATE OR REPLACE view wp_users
AS
  SELECT user_id                     AS ID,
         username                    AS user_login,
         user_password               AS user_pass,
         username_clean              AS user_nicename,
         user_email,
         user_website                AS user_url,
         FROM_UNIXTIME(user_regdate) AS user_registered,
         username                    AS display_name
  FROM   phpbb_users
  WHERE  user_type != 1
         AND user_type != 2
WITH cascaded CHECK OPTION;
```
6. Re-create *wp_usermeta* (you may be able to get away with just truncating and altering the table). Basing the following code on export from [phpMyAdmin](https://www.phpmyadmin.net/), since WordPress updates have altered the table since I first did it:
(Note that the inserts are for user_id 2. As the way the plugin is written now, you may be able to skip doing any inserts as long as you're a [Founder](https://wiki.phpbb.com/Founder))
```SQL
ALTER TABLE wp_usermeta RENAME TO wp_usermeta_bak; 

CREATE TABLE wp_usermeta (
	umeta_id bigint(20) UNSIGNED NOT NULL,
	user_id mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
	meta_key varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
	meta_value longtext COLLATE utf8mb4_unicode_520_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES
(1, 2, 'first_name', ''),
(2, 2, 'last_name', ''),
(3, 2, 'nickname', 'admin'),
(4, 2, 'description', ''),
(5, 2, 'rich_editing', 'true'),
(6, 2, 'comment_shortcuts', 'false'),
(7, 2, 'admin_color', 'fresh'),
(8, 2, 'use_ssl', '0'),
(9, 2, 'aim', ''),
(10, 2, 'yim', ''),
(11, 2, 'jabber', ''),
(12, 2, 'wp_capabilities', 'a:1:{s:13:"administrator";s:1:"1";}'),
(13, 2, 'wp_user_level', '10'),
(14, 2, 'wp_dashboard_quick_press_last_post_id', '3'),
(15, 2, 'wp_user-settings', 'm7=o'),
(16, 2, 'wp_user-settings-time', '1285144063');

ALTER TABLE wp_usermeta
  ADD PRIMARY KEY (umeta_id),
  ADD KEY user_id (user_id),
  ADD KEY meta_key (meta_key(191));

ALTER TABLE wp_usermeta MODIFY umeta_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

ALTER TABLE wp_usermeta ADD CONSTRAINT wp_usermeta_fk FOREIGN KEY(user_id) REFERENCES phpbb_users(user_id) ON DELETE CASCADE;
```
