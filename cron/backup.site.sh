#!/usr/bin/env bash

SCRIPT="${0}"
SCRIPT_BASEDIR="$(dirname ${SCRIPT})"
if [[ ${SCRIPT_BASEDIR} != '.' ]]; then
	SCRIPT_PATH=`echo ${SCRIPT_BASEDIR}`
else
	SCRIPT_PATH=`echo ${PWD}`
fi

. ${SCRIPT_PATH}/_config.conf

CLOUD_CONTAINER="BBS_SITE"

# Minimal age interval (weekly * 10 + 1)
MIN_AGE=12d

FILENAME_RAR=bbs_site_${NOW}.rar

rar a -x@${RARFILES_EXCLUDE_LIST} -idq -m5 -s -r -rr5p ${TEMP_PATH}/${FILENAME_RAR} @${RARFILES_INCLUDE_LIST}

rclone delete --config ${RCLONE_CONFIG} --min-age ${MIN_AGE} ${RCLONE_ACCOUNT}:${CLOUD_CONTAINER}/
rclone sync --config ${RCLONE_CONFIG} -L -u -v ${TEMP_PATH}/${FILENAME_RAR} ${RCLONE_ACCOUNT}:${CLOUD_CONTAINER}/

rm ${TEMP_PATH}/${FILENAME_RAR}