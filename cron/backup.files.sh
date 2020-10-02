#!/usr/bin/env bash
# Не используется. Для бэкапа сайта и storage используем backup.site.sh

. _config.conf

CLOUD_CONTAINER=BBS_UPLOAD

FILESOURCES=(

)

for SOURCE in "${FILESOURCES[@]}"
do
    rclone sync --config ${RCLONE_CONFIG} -L -u -v ${SOURCE} ${RCLONE_ACCOUNT}:${CLOUD_CONTAINER}/${SOURCE}
done


