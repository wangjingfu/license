<?php
namespace common\constant;

class Constant
{
    const LOGIN_SESSION_SAVE_NAME = "__login_unique_name__";

    const LICENSE_FILE_SAVE_PATH = '/var/pbx/tmp/storage/';

    const STATUS_USER_ENABLE = 1;

    const STATUS_USER_DISABLE = 0;

    const ERROR_CODE_REQUEST_ERROR = 100000;

    const ERROR_CODE_NOT_LOGIN = 200000;

    const ERROR_CODE_TOO_FREQUENT_OPERATION = 200001;

    const ERROR_CODE_TOO_MANY_ATTEMPTS = 200002;

    const ERROR_CODE_CAPTCHA_CHECK_ERROR = 200003;

    const ERROR_CODE_USERNAME_OR_PASSWORD_ERROR = 200004;

    const ERROR_CODE_USER_IS_FORBIDDEN = 200005;
}