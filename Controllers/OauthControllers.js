const axios = require('axios');
const fs = require('fs').promises;
const { saveTokens } = require('../services/TokenSevice');
const { callApi } = require('../services/OauthServices');

//Cài đặt ứng dụng
exports.installApp = (req, res) => {
  res.redirect(`https://${process.env.BITRIX_DOMAIN}/oauth/authorize/?client_id=${process.env.CLIENT_ID}&response_type=code&redirect_uri=${process.env.REDIRECT_URI}`);
};

//Xử lý callback lấy token
exports.handleCallback = async (req, res) => {
  const { code } = req.query;
  if (!code) return res.status(400).send('không có data birix tả về ');

  try {
    const response = await axios.post('https://oauth.bitrix.info/oauth/token', null, {
      params: {
        grant_type: 'authorization_code', 
        client_id: process.env.CLIENT_ID,
        client_secret: process.env.CLIENT_SECRET, 
        redirect_uri: process.env.REDIRECT_URI, 
        code }
    });
    await saveTokens(response.data);
     res.send('Lấy thành công token! chuyển qua contact bài 2 <a href="/contact"> xem contact ngay</a>');
  
  } catch (error) {
    res.status(500).send(`Lỗi: ${error.message}`);
  }
};

//in ra thông báo ở app trong bitrix
exports.KindInstallHandler = async (req, res) => {
  const { code, reinstall } = req.query;
  if (code) {
    return res.redirect('/api/callback?' + new URLSearchParams(req.query).toString());
  } 
  try {
    const { DOMAIN, APP_SID } = req.query;
    await fs.writeFile('inforApp.json', JSON.stringify({
      domain: DOMAIN,
      appSid: APP_SID,
      installedAt: new Date().toISOString(),
      lastUpdated: new Date().toISOString()
    }, null, 2));
    res.send(`<h1>${reinstall ? 'Đã cài đặt' : 'Đã cài lại'} thành công </h1>   <a href="/contact"> xem contact ngay</a>`);
  } catch (error) {
    console.error('lỗi :', error);
    res.status(500).send('<h1>tải thất bại</h1>');
  }
};
// Test API
exports.testApi = async (req, res) => {
  const data = await callApi('crm.contact.list');
  res.json(data);
};