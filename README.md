Create .env At Root Directory
สร้างไฟล์ .env ไว้ภายใต้ Folder หลัก ด้วยค่าต่อไปนี้ โดยแก้ไขค่าให้ถูกต้องตามที่จะใช้งาน

# -----------------------------------------------------
# Database Configuration (MariaDB)
# -----------------------------------------------------
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gpu_store
DB_USERNAME=root
DB_PASSWORD=your_secure_password_here
DB_CHARSET=utf8mb4

# -----------------------------------------------------
# Application Configuration
# -----------------------------------------------------
APP_NAME="GPU First-Hand Store"
APP_ENV=local
APP_URL=http://localhost/gpu_store

ข้อควรระวังเพิ่มเติม:

การใช้ไฟล์ .env มีข้อดีคือเป็นการแยกข้อมูลที่ละเอียดอ่อน (เช่น รหัสผ่านฐานข้อมูล) ออกจากซอร์สโค้ดหลัก

เมื่อนำไปรันบน Linux Server (Production) ควรใช้คำสั่ง chmod 600 .env ใน Terminal เพื่อจำกัดสิทธิ์ให้แก้ไขและอ่านได้เฉพาะ User ที่เป็นเจ้าของระบบเท่านั้น เพื่อป้องกันผู้ไม่หวังดีเข้ามาอ่านไฟล์นี้ครับ

อย่าลืมนำไฟล์ .env เข้าไปใส่ในไฟล์ .gitignore เพื่อป้องกันไม่ให้เผลออัปโหลดรหัสผ่านขึ้นไปบน GitHub หรือระบบ Version Control ครับ