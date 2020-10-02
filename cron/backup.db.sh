#!/usr/bin/env bash

. _config.conf

CLOUD_CONTAINER="BBS_SQL"

DATABASES=(
bodybuildingshop
)

for DB in "${DATABASES[@]}"
do
    FILENAME_SQL=${DB}_${NOW}.sql
    FILENAME_GZ=${DB}_${NOW}.gz

    mysqldump -Q --single-transaction -h "$MYSQL_HOST" "$DB" | pigz -c > ${TEMP_PATH}/${FILENAME_GZ}

    rclone delete --config ${RCLONE_CONFIG} --min-age ${MIN_AGE_DAILY} ${RCLONE_ACCOUNT}:${CLOUD_CONTAINER}/DAILY
    rclone copy --config ${RCLONE_CONFIG} -L -u -v "$TEMP_PATH"/"$FILENAME_GZ" ${RCLONE_ACCOUNT}:${CLOUD_CONTAINER}/DAILY

    # if it is a sunday (7th day of week) - make store weekly backup (42 days = 7*6 + 1, so we storing last six weeks)
    if [[ ${NOW_DOW} -eq 1 ]]; then
        rclone delete --config ${RCLONE_CONFIG} --min-age ${MIN_AGE_WEEKLY} ${RCLONE_ACCOUNT}:${CLOUD_CONTAINER}/WEEKLY
        rclone copy --config ${RCLONE_CONFIG} -L -u -v "$TEMP_PATH"/"$FILENAME_GZ" ${RCLONE_ACCOUNT}:${CLOUD_CONTAINER}/WEEKLY
    fi

    # backup for first day of month
    if [[ ${NOW_DAY} == 01 ]]; then
        rclone delete --config ${RCLONE_CONFIG} --min-age ${MIN_AGE_MONTHLY} ${RCLONE_ACCOUNT}:${CLOUD_CONTAINER}/MONTHLY
        rclone copy --config ${RCLONE_CONFIG} -L -u -v "$TEMP_PATH"/"$FILENAME_GZ" ${RCLONE_ACCOUNT}:${CLOUD_CONTAINER}/MONTHLY
    fi

    rm "$TEMP_PATH"/"$FILENAME_GZ"
done
