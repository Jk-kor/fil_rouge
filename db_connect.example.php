<?php
// 1. DB 접속 정보 세팅
$host = '127.0.0.1'; 
$dbname = 'immobilier_db'; // 방금 만든 부동산 DB
$username = 'dev_user';    // 방금 만든 만능 계정
$password = 'secretttttt'; // mettez le mot de passe que vous avez défini pour dev_user

try {
    // 2. MariaDB에 문 두드리기 (PDO 방식)
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // 3. 에러 발생 시 화면에 잘 보여주도록 설정
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 4. 연결 성공 시 출력될 메시지
    echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
    echo "<h1 style='color: #27ae60;'>🎉 DB 연결 대성공! 완벽합니다!</h1>";
    echo "<p>웹 서버(Nginx/PHP)와 MariaDB가 성공적으로 악수했습니다 🤝</p>";
    echo "<p>이제 친구들이 이 파일을 `require_once('db_connect.php');` 로 불러와서 마음껏 개발할 수 있습니다!</p>";
    echo "</div>";

} catch (PDOException $e) {
    // 5. 연결 실패 시 출력될 메시지
    echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
    echo "<h1 style='color: #c0392b;'>🚨 DB 연결 실패...</h1>";
    echo "<p>무언가 설정이 어긋났습니다. 아래 에러 메시지를 확인해 보세요:</p>";
    echo "<b style='color: #e74c3c;'>" . $e->getMessage() . "</b>";
    echo "</div>";
}
?>