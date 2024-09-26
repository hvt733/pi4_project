const express = require("express");
const path = require("path");
// const phpExpress = require('php-express')({ binPath: '/usr/bin/php' }); // Đường dẫn đến PHP binary trên máy chủ của bạn

// Import Firebase SDK cho Node.js
const { initializeApp } = require("firebase/app");
const { getDatabase, ref, set, get, child, onValue } = require("firebase/database");

const app = express();

// Middleware để phân tích dữ liệu từ các form submissions
app.use(express.urlencoded({ extended: true }));
// app.use(express.json()); // Thêm middleware để phân tích dữ liệu JSON

// Cấu hình để phục vụ các tệp tĩnh từ thư mục '/var/www/html'
app.use(express.static('/var/www/html'));

// Cấu hình Firebase
const firebaseConfig = {
    apiKey: "AIzaSyCTv0WFKuNafqszXmI40v_DIOVPkNqp-78",
    authDomain: "monitoring-sensor-4e47e.firebaseapp.com",
    databaseURL: "https://monitoring-sensor-4e47e-default-rtdb.asia-southeast1.firebasedatabase.app",
    projectId: "monitoring-sensor-4e47e",
    storageBucket: "monitoring-sensor-4e47e.appspot.com",
    messagingSenderId: "935434093765",
    appId: "1:935434093765:web:35353ee82daebe23e6dcac",
    measurementId: "G-B6MXC3TY4R"
};

function stringToBinary(str) {
    return str.split('').map(char => {
        return char.charCodeAt(0).toString(2).padStart(8, '0'); // Biến từng ký tự thành mã nhị phân 8 bit
    }).join('');
}

function xorBinary(bin1, bin2) {
    let result = '';
    for (let i = 0; i < bin1.length; i++) {
        result += bin1[i] === bin2[i % bin2.length] ? '0' : '1';
    }
    return result;
}

function binaryToHex(bin) {
    return parseInt(bin, 2).toString(16).padStart(Math.ceil(bin.length / 4), '0');
}

function reverseString(str) {
    return str.split('').reverse().join('');
}

function EncryptPassword(username, password, code) {
    const key1 = stringToBinary(username);
    const key2 = stringToBinary(password);
    const key3 = stringToBinary(code);
    const key4 = stringToBinary(username + password);
    const key5 = stringToBinary(reverseString(code));
    const key7 = stringToBinary(reverseString(password));
    const key8 = stringToBinary(reverseString(username));

    let binaryPassword = stringToBinary(username + password + code);

    binaryPassword = xorBinary(binaryPassword, key1);
    binaryPassword = xorBinary(binaryPassword, key2);
    binaryPassword = xorBinary(binaryPassword, key3);
    binaryPassword = xorBinary(binaryPassword, key4);
    binaryPassword = xorBinary(binaryPassword, key5);
    binaryPassword = xorBinary(binaryPassword, key7);
    binaryPassword = xorBinary(binaryPassword, key8);

    const hexPassword = binaryToHex(binaryPassword);
    return hexPassword;
}

const firebaseApp = initializeApp(firebaseConfig);
const db = getDatabase(firebaseApp);

app.get("/", (req, res) => {
    res.sendFile(path.join('/var/www/html/views', 'login.html'));
});

app.post("/login", (req, res) => {
    const username = req.body.username.toString().trim();
    const password = req.body.password.toString().trim();
    let code = req.body.code.toString().trim();
    const specialCharPattern = /[!@#$%^&*(),.?":{}|<>]/;

    if (specialCharPattern.test(username) || specialCharPattern.test(password)) {
        return res.json({ error: 'Username và password không được chứa ký tự đặc biệt!' });
    }

    console.log('Username:', username);
    console.log('Password:', password);
    console.log('Code:', code);

    const encryptedInputPassword = EncryptPassword(username, password, code);
    const dbRef = ref(db, 'Account Index');

    get(dbRef).then((snapshot) => {
        if (snapshot.exists()) {
            const accounts = snapshot.val();
            let isLoginSuccess = false;

            if (!code) {
                code = 1;
                if (accounts['Admin']) {
                    for (let adminName in accounts['Admin']) {
                        const adminAccount = accounts['Admin'][adminName];
                        if (adminName === username && adminAccount.Pass === encryptedInputPassword) {
                            console.log('Login success with admin account:', adminName);
                            isLoginSuccess = true;
                            break;
                        }
                    }
                }
            } else {
                if (accounts['Users']) {
                    for (let userUsername in accounts['Users']) {
                        const userAccount = accounts['Users'][userUsername];
                        if (userUsername === username && userAccount.Pass === encryptedInputPassword) {
                            console.log('Login success with user account:', userUsername);
                            isLoginSuccess = true;
                            break;
                        }
                    }
                }
            }

            if (isLoginSuccess) {
                res.redirect("/home");
            } else {
                res.json({ error: 'Login failed with account: ' + username });
            }

        } else {
            console.log('Không có người dùng nào trong cơ sở dữ liệu');
            res.json({ error: 'Không có người dùng nào trong cơ sở dữ liệu' });
        }
    }).catch((error) => {
        console.error('Lỗi khi truy xuất dữ liệu:', error);
        res.json({ error: 'Lỗi khi truy xuất dữ liệu: ' + error });
    });
});

app.get("/home", (req, res) => {
    res.sendFile(path.join('/var/www/html/views', 'home.html'));
});

const port = process.env.PORT || 2000;
app.listen(port, () => {
    console.log("Server is running: http://localhost:" + port);
});
