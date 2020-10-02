#
# Regular cron jobs for the bodybuildingshop package
#

#*/5	*	*	*	*	www-data	/bin/bash		/var/www/bbs/cron.update.sitemap.sh

# Do backup database

# Do backup storage

0       4       *       *       *       root    /var/www/bbs/cron/backup.db.sh  >> /var/log/backup.sql.log 2>&1
10      4       *       *       *       root    /var/www/bbs/cron/backup.site.sh  >> /var/log/backup.site.log 2>&1
