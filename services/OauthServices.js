const axios = require('axios');
const fs = require('fs').promises;
const { loadTokens, saveTokens, refreshToken } = require('./TokenSevice');


exports.callApi = async (action, payload = {}) => {
  let tokens = await loadTokens();
  if (!tokens) throw new Error('Không có token!');

  try {
    const res = await axios.get(`https://${process.env.BITRIX_DOMAIN}/rest/${action}`, {
      params: { auth: tokens.access_token, ...payload },
      timeout: 5000,
    });
    return res.data;
  } catch (err) {
    if (err.response?.data?.error === 'expired_token') {
      tokens.access_token = await refreshToken();
      await saveTokens(tokens);
      return exports.callApi(action, payload);
    }
    throw new Error(`API lỗi: ${err.message}`);
  }
};