<!DOCTYPE html>
<html lang="pt-BR">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>NoBusão - Cadastro ADM</title>


<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
}


body{

    background:#0b0b0f;
    color:white;

}


.top-bar{

    width:100%;
    background:#15151c;
    padding:25px 40px;
    border-bottom:1px solid #1f1f2e;

}


.container{

    max-width:600px;
    margin:auto;

}


h1{

    margin-bottom:25px;

}


h1 span{

    color:#007bff;

}


form{

    background:#15151c;
    padding:25px;

    border-radius:8px;

    border:1px solid #1f1f2e;

    border-left:4px solid #007bff;

}


.form-group{

    margin-bottom:18px;

}


label{

    display:block;

    margin-bottom:7px;

    color:#b3b3b3;

    font-size:14px;

}


input,
select{

    width:100%;

    padding:12px;

    background:#282842;

    color:white;

    border:1px solid #282842;

    border-radius:6px;

    font-size:15px;

}



input:focus,
select:focus{

    border-color:#007bff;

    outline:none;

}



input[type="file"]{

    padding:10px;

}



button{

    width:100%;

    height:45px;

    background:#007bff;

    color:white;

    border:none;

    border-radius:6px;

    font-weight:bold;

    cursor:pointer;

}



button:hover{

    background:#5893ff;

}



.voltar{

    display:block;

    margin-top:20px;

    text-align:center;

    color:#007bff;

    text-decoration:none;

}


</style>

</head>
<body>


<div class="top-bar">

<div class="container">


<h1>
No<span>Busão</span>

<form 
action="salvar_adm.php" 
method="POST"
enctype="multipart/form-data">


<div class="form-group">

<label>Nome:</label>
<input type="text"name="nome"required>

</div>

<div class="form-group">

<label>Email:</label>
<input type="email"name="email"required>

</div>

<div class="form-group">

<label>Senha:</label>
<input type="password"name="senha"required>

</div>

<button type="submit">
Entrar
</button>

<a href="index.html" class="voltar">
<h5>Voltar para consulta</h5>

</button>
</form>
</div>
</div>

</body>
</html>
