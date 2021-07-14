# Multi_RSS_Combiner
markcain@markcain.com
July 11, 2021 - 0.1.3


      __  __                  _           ____           _
     |  \/  |   __ _   _ __  | | __      / ___|   __ _  (_)  _ __
     | |\/| |  / _` | | '__| | |/ /     | |      / _` | | | | '_ \
     | |  | | | (_| | | |    |   <      | |___  | (_| | | | | | | |
     |_|  |_|  \__,_| |_|    |_|\_\      \____|  \__,_| |_| |_| |_|




This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 3 of the License, or any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.


PHP Script to combine all of the rss feeds associated with Destination Linux Network into a single RSS feed.  This is basic procedural PHP
  - Each RSS feed of the DLN Network is read.
  - Each \<item\> (podcast episode details) is stored in a master array.
  - Each <title> of each <item> has the title of the podcast added as some titles don't have the name of the podcast (the lack of uniformity is noted)
  - The master array is sorted by published date of each individual podcast
  - A boiler plate header which includes the xml namespace specs of the various podcasts is added to the new RSS feed
  - A custom <channel> header is created for the new RSS feed -- NOTE: the contents of rawvoice needs to be reviewed
  - Each <item> (podcast details) is added by date to the new RSS feed
  - The footer of the RSS feed is added to the new RSS feed.
  - an xlm file will be generated into the specified folder
  - add a line to the crontab on the server to run this script on an hourly basis.  Such as:
      -     00 * * * * /usr/local/bin/php /home/markcain/public_html/feed/generate_master_rss.php
