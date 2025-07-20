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
      }
          // Lấy thêm requisite.bank
      const bankdetailRes = await axios.post(`${tokens.client_endpoint}crm.requisite.bankdetail.list`, {
        filter: {  ENTITY_ID: requisite.ID },
        auth: tokens.access_token
      });

      const bankdetail = bankdetailRes.data.result?.[0];
        
      if (bankdetail) {
        contactObj.bankName = bankdetail.RQ_BANK_NAME || "Không có tên ngân hàng";
        contactObj.bankAccount = bankdetail.RQ_ACC_NUM || "Không có số tài khoản";
        contactObj.bankAddress = bankdetail.RQ_BANK_ADDR || "không co schi nhánh";
      }
     console.log(contactObj);
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
      }
    });


    const requisiteRes = await callApi('crm.requisite.list', {
      filter: {
        ENTITY_TYPE_ID: 3,
        ENTITY_ID: contactId
      }
    });

    const requisiteId = requisiteRes.result?.[0]?.ID;

    if (!requisiteId) {
      throw new Error("❌ Không tìm thấy requisite của contact.");
    }
;

    tokens= await loadTokens();
 await axios.post(
  `${tokens.client_endpoint}crm.requisite.bankdetail.add`,
  {
    auth: tokens.access_token,
    fields: {
      ENTITY_ID: Number(requisiteId),
      COUNTRY_ID: 84,
      NAME: "THÔNG TIN NGÂN HÀNG",
      RQ_BANK_NAME: fields.RQ_BANK_NAME || "Default Bank",
      RQ_BANK_ADDR: fields.RQ_BANK_ADDR || "Default Address",
      RQ_ACC_NUM: fields.RQ_ACC_NUM || "123456789",
      ACTIVE: 'Y',
      SORT: 500
    }
  },
  {
    headers: { "Content-Type": "application/json" }
  }
);



      res.redirect("http://localhost:3000/contact");
  } catch (err) {
    console.error(err);
    res.status(500).send(err.message);
  }
};

exports.edit = async (req, res) => {
  const { id } = req.params;

  const response = await callApi('crm.contact.get', { id });
    let contact = response.result || {};
const requisiteList = await callApi('crm.requisite.list', {
  filter: { ENTITY_TYPE_ID: 3, ENTITY_ID: contact.ID }
});
const requisite = requisiteList.result?.[0];
  const bankdetailRes = await callApi(
    'crm.requisite.bankdetail.list',
    { filter: { ENTITY_ID: requisite.ID } }
  );
  const bankdetail = bankdetailRes.result?.[0] || {};

  
  contact.email = requisite.RQ_EMAIL || "không có MAIL";
  contact.phone = requisite.RQ_PHONE || "Không có phone";
  contact.website = requisite.RQ_CONTACT || "Không web";
  contact.bankName = bankdetail.RQ_BANK_NAME || "Không có tên ngân hàng";
  contact.bankAccount = bankdetail.RQ_ACC_NUM || "Không có số tài khoản";
  contact.bankAddress = bankdetail.RQ_BANK_ADDR || "Không có chi nhánh";

  res.render("edit", { contact });
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
        RQ_CONTACT:fields.WEB
    };

    if (requisite) {
     
      await axios.post(`${tokens.client_endpoint}crm.requisite.update`, {
        id: requisite.ID,
        fields: requisiteFields,
        auth: tokens.access_token
      });
      console.log(`✅ Đã update requisite ID: ${requisite.ID}`);
    
    }

  const bankdetailRes = await axios.post(`${tokens.client_endpoint}crm.requisite.bankdetail.list`, {
        filter: {  ENTITY_ID: requisite.ID },
        auth: tokens.access_token
      });

      const bankdetail = bankdetailRes.data.result?.[0];
    const bankdetailFields = {
       RQ_BANK_NAME: fields.RQ_BANK_NAME ,
      RQ_BANK_ADDR: fields.RQ_BANK_ADDR ,
      RQ_ACC_NUM: fields.RQ_ACC_NUM
      
    };

    if (bankdetail) {
      // Nếu đã có requisite -> update
      await axios.post(`${tokens.client_endpoint}crm.requisite.bankdetail.update`, {
        id: bankdetail.ID,
        fields: bankdetailFields,
        auth: tokens.access_token
      });
      console.log(`✅ Đã update bankdetail ID: ${bankdetail.ID}`);
    
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
