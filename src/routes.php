<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {
    $container = $app->getContainer();

    $app->get('/[{name}]', function (Request $request, Response $response, array $args) use ($container) {
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");

        // Render index view
        return $container->get('renderer')->render($response, 'index.phtml', $args);
    });
	
	//POST Register Daftar
	$app->post('/user/daftar', function (Request $request, Response $response){
		
		$nama 		= $request->getParsedBodyParam("nama");
		$hp 		= $request->getParsedBodyParam("hp");
		$email 		= $request->getParsedBodyParam("email");
		$password 	= $request->getParsedBodyParam("password");
		
		$query = "INSERT INTO tb_user (nama, hp, email, `password`) VALUES ('$nama', '$hp', '$email', MD5('$password'))";
		
		if (empty($nama) || empty($hp) || empty($email) || empty($password)){
			return $response->withJson(["meta" => ["code" => 201, "message"=>"Lengkapi data Anda"]]);
		}
		
		$query_hp 	= "SELECT * FROM tb_user WHERE hp = '$hp'"; //ngeccek No Hape Sudah Terdaftar
		$stmt		= $this->db->prepare($query_hp);
		if ($stmt->execute()) {
			$result = $stmt->fetchAll();
			if($result <> null) {
				return $response->withJson(["meta"=> ["code"=>201, "message"=>"No HP Sudah Terdaftar"]]);
			}
		}
		
		$query_email 	= "SELECT * FROM tb_user WHERE email = '$email'"; //ngeccek Email Sudah Terdaftar
		$stmt2			= $this->db->prepare($query_email);
		if ($stmt2->execute()) {
			$result2 = $stmt2->fetchAll();
			if($result2 <> null) {
				return $response->withJson(["meta"=> ["code"=>201, "message"=>"Email Sudah Terdaftar"]]);
			}
		}
		
		$stmt = $this->db->prepare($query);
		
		if ($stmt->execute()) {
			return $response->withJson(["meta"=> ["code"=>200, "message"=>"Data Berhasil Disimpan"]]);
		}
		return $response->withJson(["meta"=> ["code"=>201, "message"=>"Data Gagal Disimpan"]]);
		
	});
	
	//LOGIN
	 $app->post("/user/login", function (Request $request, Response $response){
        $inputvalue = $request->getParsedBody();

        $query  = "SELECT * FROM tbl_user WHERE nama = :nama AND password = :password";

        $data = [
            ":nama"         => $inputvalue["nama"],
            ":password"     => $inputvalue["password"]
        ];

        $stmt = $this->db->prepare($query);
        if($stmt->execute($data)){
            $row		= $stmt->fetch(PDO::FETCH_NUM);
			$id			= $row['0'];
			$nama		= $row['1'];
			$email		= $row['2'];
			
			if($id !=null ){
				return $response->withJson(["code" => 200, "meta" => "Login Berhasil", "data" => ["id" => "$id", "nama" => "$nama", "email" => "$email"]],200);
			}
			
			return $response->withJson(["code" => 201, "meta" => "Gagal Login Username atau password salah !"],200);
        }
  
    });

    //SELECT ALL
    $app->get("/user/", function (Request $request,Response $response){
        $query = "SELECT * FROM tbl_user";
        $stmt   = $this->db->prepare($query);

        if($stmt->execute()){
            $result = $stmt->fetchAll();
            if($result){
                return $response->withJson(["code" => 200, "meta"=>"Data ditemukan","data"=>$result], 200);
            }
            return $response->withJson(["code" => 200, "meta"=>"Data ditemukan","data"=>$result], 200);
        }
    });
    

    //SELECT BY NAME
    $app->get("/user/{nama}", function (Request $request,Response $response, $args){

        $nama = $args['nama'];
        $query = "SELECT * FROM tbl_user where nama = '$nama'";
        $stmt   = $this->db->prepare($query);

        if($stmt->execute()){
            $result = $stmt->fetchAll();
            if($result){
                return $response->withJson(["code" => 200, "meta"=>"Data ditemukan","data"=>$result], 200);
            }
            return $response->withJson(["code" => 201, "meta"=>"Data tidak ditemukan","data"=>$result], 200);
        }
    });

    //


    //INSERT
    $app->post("/user", function (Request $request, Response $response){
        $inputvalue = $request->getParsedBody();

        $query  = "INSERT INTO `tbl_user`( `nama`, `email`, `password`, `timestamp`)\n"."VALUES\n"."( :nama, :email, :password, CURRENT_TIMESTAMP);";

        $data = [
            ":nama"         => $inputvalue["nama"],
            ":email"        => $inputvalue["email"],
            ":password"     => $inputvalue["password"]
        ];

        $stmt = $this->db->prepare($query);
        if($stmt->execute($data)){
            return $response->withJson([
                        "code" => 200, 
                        "meta" => "Tambah data berhasil"],
                         200);
        }
        return $response->withJson(["code" => 201, "meta" => "Data Kosong"],200);
    });


    //DELETE
    $app->delete("/user/{id}", function (Request $request, Response $response, $args){
        $id = $args['id'];

        $query = "DELETE FROM tbl_user where id = :id";
        $stmt = $this->db->prepare($query);

        $data = [
            ":id" => $id
        ];

        if($stmt->execute($data)){
            return $response->withJson(["code" => 200, "meta" => "Hapus data berhasil"],200);
        }
        return $response->withJson(["code" => 201, "meta" => "Hapus data gagal"],200);
    });


    //UPDATE
    $app->post("/user/{id}", function(Request $request, Response $response, $args){
        $id = $args['id'];
        // $inputvalue = $request->getParsedBody();
        // $stmt = $this->db->prepare($query);
        $query = "SELECT * FROM tbl_user WHERE id = :id";
        // $stmt = $this->db->prepare($query);
        $data = [
            ":id"       => $id
        ];
        $stmt = $this->db->prepare($query);
        if($stmt->execute($data)){
            $result = $stmt->fetchAll();
            if($result){
                $query = "UPDATE tbl_user set `nama` = :nama, `email` = :email, `password` = :password, timestamp = CURRENT_TIMESTAMP where id = :id";
                $stmt = $this->db->prepare($query);
                $inputvalue = $request->getParsedBody();
            
                $data = [
                    ":id"       => $id,
                    ":nama"     => $inputvalue["nama"],
                    ":email"    => $inputvalue["email"],
                    ":password" => $inputvalue["password"]
                ];
       
        if($stmt->execute($data)){
            return $response->withJson(["code" => 200, "meta" => "Update data berhasil"],200);
        }
            return $response->withJson(["code" => 201, "meta" => "Update data gagal!!"],200);
    }else{
        return $response->withJson(["code" => 201, "meta" => "Update data gagal!!"],200);
    }
}
    });

};

   
