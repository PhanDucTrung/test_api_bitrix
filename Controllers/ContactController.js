const { callApi } = require('../services/OauthServices');
const { loadTokens ,refreshToken } = require('../services/TokenSevice');
const axios = require('axios');


  exports.getContacts = async (req, res) => {
      try {
          const tokens = await loadTokens();
          if (!tokens || !tokens.access_token) throw new Error('❌ Không có token hợp lệ!');

          // Lấy danh sách contact
          const response  = await axios.get(`${tokens.client_endpoint}crm.contact.list`, {
              params: {   
                  auth: tokens.access_token,
                  select: ["ID", "NAME", "EMAIL", "PHONE", "WEB", "SOURCE_DESCRIPTION", "ADDRESS"]
              }
          });
          const contactsData   = response.data.result || [];
       
        const contacts= contactsData.map(contact=>{
          return{        
                  id: contact.ID,
                  name: contact.NAME,
                  email: contact.EMAIL?.[0]?.VALUE || "Không có email",
                  phone: contact.PHONE?.[0]?.VALUE || "Không có số điện thoại",
                  website: contact.WEB || "Không có website",
                  address: contact.ADDRESS || "Không có địa chỉ",
                  bankInfo: contact.SOURCE_DESCRIPTION || "Không có thông tin ngân hàng"
          }
        })
    
 
    res.render("home", { Contacts: contacts });   
      } catch (error) {
          console.error('❌ Lỗi gọi API contact:', error.response?.data || error.message);
          res.status(500).json({ error: error.message });
      }
  };
//chuyển huowng sang trang tạo contact
 exports.create= async(req, res) =>{
     res.render('create');
    };

// POST: create
exports.createContact = async (req, res) => {
  const fields = req.body;
  try {
    const data = await callApi('crm.contact.add', { fields });
  
    
   res.redirect("http://localhost:3000/contact");
  } catch (err) {
    res.status(500).send(err.message);
  }
};

exports.edit= async(req, res)=>{
    const {id}  = req.params;
    const response = await callApi('crm.contact.get', {id});

      const contact  = response.result || [];
console.log(contact);
    res.render("edit",{ contact : contact});
};

// PUT: update
exports.updateContact = async (req, res) => {
 
  const{id}  = req.params;
  const fields = req.body;
  console.log(fields);
  try {
    const data = await callApi('crm.contact.update', { id, fields: fields});

   res.redirect("http://localhost:3000/contact");
  } catch (err) {
    res.status(500).send(err.message);
  }
};

// DELETE
exports.deleteContact = async (req, res) => {
  console.log("da vado");
  const { id } = req.params;
   console.log(id)
  try {
    const data = await callApi('crm.contact.delete', { id });
   res.redirect("http://localhost:3000/contact");
  } catch (err) {
    res.status(500).send(err.message);
  }
};
