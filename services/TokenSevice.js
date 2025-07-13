
const fs = require('fs').promises;



//Lưu trữ token vào file tokens.json
exports.saveTokens = async (tokens) => await fs.writeFile('tokens.json', JSON.stringify(tokens, null, 2));

//Đoc token từ file tokens.json
exports.loadTokens = async () => {
  try {
    return JSON.parse(await fs.readFile('tokens.json', 'utf8'));
  } catch {
    return null;
  }
};

exports.refreshToken = async () => {
  const tokens = await loadTokens();
  if (!tokens?.refresh_token) throw new Error('Không có token mới');

  try {
    const res = await axios.post('https://oauth.bitrix.info/oauth/token', null, {
      params: {
        grant_type: 'refresh_token',
        client_id: process.env.CLIENT_ID,
        client_secret: process.env.CLIENT_SECRET,
        refresh_token: tokens.refresh_token,
      }
    });
    await saveTokens(res.data);
    return res.data.access_token;
  } catch (err) {
    await fs.unlink('tokens.json');
    throw new Error('Vui lòng cài đặt lại ứng dụng!');
  }
};


