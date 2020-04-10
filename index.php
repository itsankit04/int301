<?php
    include('config.php');
    if(isset($_POST['search'])) {
        $regno = $_POST['regno'];
        $stmt = $conn->prepare("SELECT perc FROM result WHERE regno = ?");
        $stmt->bind_param("i", $regno);
        $stmt->execute();
        $stmt->bind_result($perc);
        $stmt->fetch();
        $stmt->close();
        if(empty($perc)) {
            $newuser = true;
        }
    }
    if(isset($_POST['newuser'])) {
        $regno = $_POST['regno'];
        $subjectname = $_POST['sname'];
        $subjectmarks = $_POST['smarks'];
        $subjectotal = $_POST['stotal'];
        $obtained = 0;
        $total = 0;
        if(!empty($_POST['sname'])) {
            foreach($subjectname as $key=>$val){
                $stmt = $conn->prepare("INSERT INTO `subjectwise`(`regno`, `subname`, `submarks`, `subtotal`) VALUES (?,?,?,?)");
                $stmt->bind_param("isii",$regno, $subjectname[$key], $subjectmarks[$key], $subjectotal[$key]);
                $stmt->execute();
                $stmt->close();
                $obtained = $obtained + $subjectmarks[$key];
                $total = $total + $subjectotal[$key];
            }
            $perc = ($obtained / $total) * 100;
            $stmt = $conn->prepare("INSERT INTO `result`(`regno`, `perc`) VALUES (?,?)");
            $stmt->bind_param("ii",$regno, $perc);
            $stmt->execute();
            $stmt->close();
        }
    }
    if(isset($_POST['reset'])) {
        header("Location: index.php");
    }
?>
<html>
<head>
	<link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<style>
	.main {
	   width: 50%;
	   margin: 50px auto;
	}
	.has-search .form-control {
	   padding-left: 2.375rem;
	}

	.has-search .form-control-feedback {
	   position: absolute;
	   z-index: 2;
	   display: block;
	   width: 2.375rem;
	   height: 2.375rem;
	   line-height: 2.375rem;
	   text-align: center;
	   pointer-events: none;
	   color: #aaa;
	}
    .remove {
        background: #f1f1f1;
    }
    .remove td {
        font-style: italic;
    }
    .table > tbody > tr > td {
        vertical-align: middle;
    }
    .input-group {
        width: 100%;
    }
    .editable {
        border: none;
        padding: 0;
        margin: 0;
        text-shadow: none;
        box-shadow: none;
    }
    .addBtn {
        cursor: pointer;
    }
	</style>
	<title>Percentage Calculator</title>
</head>
<body>
	<div class="main">
        <h1 class="text-center mb-4">Percentage Calculator</h1>
        <?php if (isset($newuser) && $newuser) { ?>
        <form method="POST" action="">
            <div class="input-group">
                <input class="form-control" placeholder="Registration Number" type="number" name="regno" value="<?php echo $regno; ?>">
            </div>
            <div class="col-md-12">
                <table class="table">
                    <thead>
                        <tr>
                        <th>Subject Name</th>
                        <th>Marks Obtained</th>
                        <th>Total Marks</th>
                        <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="addRow">
                        <td class="col-xs-3">
                            <input class="form-control addMain" type="text" placeholder="Eg. C Programming" />
                        </td>
                        
                        <td class="col-xs-3">
                            <input class="form-control addPrefer" type="number" placeholder="Eg. 46" />
                        </td>
                        <td class="col-xs-5">
                            <input class="form-control addCommon" type="number" placeholder="Eg. 70" />
                        </td>
                        <td class="col-xs-1 text-center">
                            <span class="addBtn">
                                <i class="fa fa-plus"></i>
                            </span>
                        </td>
                        </tr>
                    </tbody>
                </table>
                <div class="col-md-3 pull-right row">
                    <button class="btn btn-danger pull-right" type="submit" name="reset">Reset</button>
                    <button class="btn btn-primary pull-right ml-2" type="submit" name="newuser">Save</button>
                </div>
            </div>
        </form>
        <?php } elseif (!empty($perc)) { ?>
            <div class="col-md-12">
            <table class="table table-bordered">
                <thead> 
                        <tr>
                            <th colspan="2">Registration Number</th>
                            <td><?php echo $regno; ?></td>
                        </tr>
                        <tr>
                            <th colspan="2">Percentage</th>
                            <td><?php echo number_format((float)$perc,2,'.',''); ?>%</td>
                        </tr>
                        <tr>
                            <th>Subject Name</th>
                            <th>Marks Obtained</th>
                            <th>Total Marks</th>
                        </tr>
                        <?php
                            $total = 0;
                            $obtained = 0;
                            $stmt = $conn->prepare("SELECT * FROM subjectwise WHERE regno = ?");
                            $stmt->bind_param("i", $regno);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $stmt->fetch();
                            $stmt->close();
                        ?>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['subname']; ?></td>
                        <td><?php echo $row['submarks']; ?></td>
                        <td><?php echo $row['subtotal']; ?></td>
                    </tr>
                    <?php $total = $total + $row['subtotal']; $obtained = $obtained + $row['submarks'];  } ?>
                    <tr>
                        <th>Total</th>
                        <td><?php echo $obtained; ?></td>
                        <td><?php echo $total; ?></td>
                    </tr>
                </tbody>
            </table>
            </div>
        <?php } else { ?>
        <form method="POST" action="">
            <div class="input-group">
                <input class="form-control" placeholder="Registration Number" type="number" name="regno">
                <div class="input-group-append">
                    <button class="btn btn-secondary" type="submit" name="search"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </form>
        <?php } ?>
	</div>
    <script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
    <script>
        function formatRows(main, prefer, common) {
            return '<tr><td class="col-xs-3"><input type="text" name="sname[]" value="' +main+ '" class="form-control editable" /></td>' +
                    '<td class="col-xs-3"><input type="text" name="smarks[]" value="' +prefer+ '" class="form-control editable" /></td>' +
                    '<td class="col-xs-3"><input type="text" name="stotal[]" value="' +common+ '" class="form-control editable" /></td>' +
                    '<td class="col-xs-1 text-center"><a href="#" onClick="deleteRow(this)">' +
                    '<i class="fa fa-trash-o" aria-hidden="true"></a></td></tr>';
        };

        function deleteRow(trash) {
            $(trash).closest('tr').remove();
        };

        function addRow() {
            var main = $('.addMain').val();
            var preferred = $('.addPrefer').val();
            var common = $('.addCommon').val();
            $(formatRows(main,preferred,common)).insertAfter('#addRow');
            $(input).val('');  
        }

        $('.addBtn').click(function()  {
            addRow();
        });
    </script>
</body>
</html>
