const express = require("express");
const dotenv = require("dotenv");
const ngrok = require("ngrok");
const fs = require("fs");
const path = require('path');
const exphbs = require("express-handlebars");
const methodOverride =require("method-override");

dotenv.config();

const oauthRoutes = require("./routes/OauthRoutes");
const contactRoutes = require('./routes/ContactRoutes');


const app = express();
const PORT = 3000;

app.use(methodOverride('_method'));
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(path.join(__dirname, 'public')));


// Cấu hình view engine
app.engine(
  "hbs",
  exphbs.engine({
    extname: ".hbs",
    defaultLayout: "main",
    layoutsDir: path.join(__dirname, "views", "layouts"),
  })
);
app.set("view engine", "hbs");
app.set("views", path.join(__dirname, "views"));

app.use("/contact", contactRoutes);
app.use("/", oauthRoutes);

app.listen(PORT, async () => {
  console.log(`đang chạy máy chủ ở http://localhost:${PORT}`);

  // Khởi động ngrok
  const ngrokUrl = await ngrok.connect({
    addr: PORT,
    authtoken: process.env.NGROK_AUTH_TOKEN,
  });

  console.log(`🌐 Ngrok URL: ${ngrokUrl}`);

  // Ghi lại REDIRECT_URI mới vào .env
  const envConfig = dotenv.parse(fs.readFileSync(".env"));
  envConfig.REDIRECT_URI = `${ngrokUrl}/callback`;

  const updatedEnv = Object.entries(envConfig)
    .map(([key, val]) => `${key}=${val}`)
    .join("\n");

  fs.writeFileSync(".env", updatedEnv);
  console.log(` ddaonj mã chạy cần chạy là  ${envConfig.REDIRECT_URI}`);
});
