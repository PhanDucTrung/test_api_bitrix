const { callApi } = require('../services/OauthServices');
const { loadTokens ,refreshToken } = require('../services/TokenSevice');
const axios = require('axios');


  

//chuyển huowng sang trang tạo contact
 
exports.getContacts = async (req, res) => {
  try {
    const tokens = await loadTokens();
    if (!tokens || !tokens.access_token) throw new Error('❌ Không có token hợp lệ!');

    // Lấy danh sách contact
    const response = await axios.get(`${tokens.client_endpoint}crm.contact.list`, {
      params: {
        auth: tokens.access_token,
        select: ["ID", "NAME", "ADDRESS"]
      }
    });
    const contactsData = response.data.result || [];

    // map contacts + bổ sung requisite
    const contacts = await Promise.all(contactsData.map(async contact => {
      const contactObj = {
        id: contact.ID,
        name: contact.NAME,
        address: contact.ADDRESS || "Không có địa chỉ",
      };

      // Lấy thêm requisite
      const requisiteRes = await axios.post(`${tokens.client_endpoint}crm.requisite.list`, {
        filter: { ENTITY_TYPE_ID: 3, ENTITY_ID: contact.ID },
        auth: tokens.access_token
      });
    
      const requisite = requisiteRes.data.result?.[0];
      if (requisite) {
        contactObj.email= requisite.RQ_EMAIL || "Không CÓ MAIL";
        contactObj.phone= requisite.RQ_PHONE || "Không CÓ MAIL";
        contactObj.website= requisite.RQ_CONTACT || "Không web";
        contactObj.bankName = requisite.RQ_COMPANY_NAME || "Không có tên ngân hàng";
        contactObj.bankAccount = requisite.RQ_COMPANY_ID || "Không có số tài khoản";
    
      }
     
      return contactObj;
    }));
    res.render("home", { Contacts: contacts });

  } catch (error) {
    console.error('❌ Lỗi gọi API contact:', error.response?.data || error.message);
    res.status(500).json({ error: error.message });
  }
};


exports.create= async(req, res) =>{
     res.render('create');
    };

exports.createContact = async (req, res) => {
  const fields = req.body;
  try {
    const contactRes = await callApi('crm.contact.add', { fields });
    const contactId = contactRes.result;

    // thêm requisite
  
await callApi('crm.requisite.add', {
      fields: {
        ENTITY_TYPE_ID: 3,
        ENTITY_ID: contactId,
        PRESET_ID: 1,
        NAME: 'Thông tin bổ xung',
        RQ_EMAIL:fields.EMAIL,
        RQ_PHONE:fields.PHONE,
        RQ_CONTACT:fields.WEB,
        RQ_COMPANY_NAME: fields.RQ_BANK_NAME ,
       RQ_COMPANY_ID: fields.RQ_ACC_NUM 
      }
    });


  

    
    // // 2. Thêm bank detail vào requisite
    // await callApi('crm.requisite.bankdetail.add', {
    //   fields: {
    //     ENTITY_ID: requisiteRes.result, 
    //     RQ_BANK_NAME: fields.RQ_BANK_NAME,
    //     RQ_ACC_NUM: fields.RQ_ACC_NUM,
    //   }
    // });

// console.log(JSON.stringify({
//   auth: tokens.access_token,
//   fields: {
//     ENTITY_ID: requisiteId,
//     COUNTRY_ID: 1,
//     RQ_BANK_NAME: fields.RQ_BANK_NAME || "",
//     RQ_BANK_ADDR: fields.RQ_BANK_ADDR || "",
//     RQ_ACC_NUM: fields.RQ_ACC_NUM || "",
//     ACTIVE: "Y",
//     SORT: 500
//   }
// }, null, 2));

//   await axios.post(
//   `https://${process.env.BITRIX_DOMAIN}/rest/${tokens.user_id}/${tokens.access_token}/crm.requisite.bankdetail.add`,
//   {
//     auth: tokens.access_token,
//     fields: {
//       ENTITY_ID: Number(requisiteId),
//       COUNTRY_ID: 1,
//       RQ_BANK_NAME: fields.RQ_BANK_NAME || "Default Bank",
//       RQ_BANK_ADDR: fields.RQ_BANK_ADDR || "Default Address",
//       RQ_ACC_NUM: fields.RQ_ACC_NUM || "123456789", // tránh để rỗng
//       ACTIVE: 'Y',
//       SORT: 500
//     }
//   },
//   {
//     headers: {
//       "Content-Type": "application/json"
//     }
//   }
// );


      res.redirect("http://localhost:3000/contact");
  } catch (err) {
    console.error(err);
    res.status(500).send(err.message);
  }
};


exports.edit= async(req, res)=>{
    const {id}  = req.params;
    const response = await callApi('crm.contact.get', {id});

      const contact  = response.result || [];
    res.render("edit",{ contact : contact});
};

// PUT: update

 exports.updateContact = async (req, res) => {
  const { id } = req.params;
  const fields = req.body;


  try {
    const data = await callApi('crm.contact.update', { id, fields: fields});

     // Load tokens để gọi API trực tiếp
    const tokens = await loadTokens();
  // Kiểm tra xem contact đã có requisite chưa
    const requisiteRes = await axios.post(`${tokens.client_endpoint}crm.requisite.list`, {
      filter: { ENTITY_TYPE_ID: 3, ENTITY_ID: id },
      auth: tokens.access_token
    });

    const requisite = requisiteRes.data.result?.[0];

    const requisiteFields = {
        RQ_EMAIL:fields.EMAIL,
        RQ_PHONE:fields.PHONE,
        RQ_CONTACT:fields.WEB,
        RQ_COMPANY_NAME: fields.RQ_BANK_NAME, 
        RQ_COMPANY_ID: fields.RQ_ACC_NUM,  
    };

    if (requisite) {
      // Nếu đã có requisite -> update
      await axios.post(`${tokens.client_endpoint}crm.requisite.update`, {
        id: requisite.ID,
        fields: requisiteFields,
        auth: tokens.access_token
      });
      console.log(`✅ Đã update requisite ID: ${requisite.ID}`);
    
    }

    res.redirect("http://localhost:3000/contact");
  } catch (err) {
    console.error('❌ Lỗi update contact:', err.response?.data || err.message);
    res.status(500).send(err.message);
  }
};



// DELETE
exports.deleteContact = async (req, res) => {

  const { id } = req.params;

  try {
    const data = await callApi('crm.contact.delete', { id });
   res.redirect("http://localhost:3000/contact");
  } catch (err) {
    res.status(500).send(err.message);
  }
};
