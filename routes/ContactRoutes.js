const express = require('express');
const router = express.Router();
const { getContacts, createContact,updateContact, deleteContact,create,edit} = require('../Controllers/ContactController');


router.get('/create', create);

router.get('/:id',edit);

router.put('/:id',updateContact);

router.get('/', getContacts);

router.post('/',createContact);

router.delete('/:id', deleteContact);

module.exports = router;
