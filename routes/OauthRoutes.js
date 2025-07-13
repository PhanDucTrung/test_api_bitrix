const express = require('express');
const router = express.Router();
const { installApp, handleCallback, KindInstallHandler ,testApi} = require('../Controllers/OauthControllers');

router.get('/install', installApp);
router.get('/callback', handleCallback);
router.get('/test-api', testApi);
router.use('/', KindInstallHandler);

module.exports = router;