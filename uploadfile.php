<script>
        function getInputValue(){
            // Selecting the input element and get its value
            var inputVal = document.getElementById("cname").value;

            // Displaying the value
            if(inputVal !== ''){
            window.open('searchName.php?&CN='+inputVal,'_bank','height=200,width=700,top=200,left=100');
            }else{
                alert('Search Client');
            }

        }
        function getclientAccount(){
            // Selecting the input element and get its value
            var inputclient = document.getElementById("cid1").value;
            // Displaying the value
            if(inputclient !== ''){
            window.open('searchAN.php?&cid1='+inputclient,'_bank','height=200,width=300,top=300,left=1000');
            }else{
                alert('Please Select Client');
            }

    }

      </script>
<?php

        if(isset($_POST['submit']))
        {
        if (!empty($_POST['cid1']) && !empty($_POST['accno1'])) {
          $cid = $_POST['cid1'];
        $accno = $_POST['accno1'];
        $scanType = $_POST['ScanType'];
        if ($scanType != 'no') {

        include('connection.php');
        set_time_limit(0);
        ignore_user_abort(1);
        $sql = "SELECT ClientID,
                        REPLACE(LastName,'/',' '),
                        REPLACE(FirstName,'/',' '),
                        REPLACE(MiddleName,'/',' '),
                        ClientName,
                        REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(ClientName,'/',' '),', ','_'),' ','_'),',','_'),' , ','_')
                        ClientFormat,
                        clientType
                FROM cifclient where ClientID=$cid";
        $stmt = sqlsrv_query( $conn, $sql);
            if( $stmt === false) {
            die( print_r( sqlsrv_errors(), true) );
        }
        $row = sqlsrv_fetch_array($stmt);
          if($row === null){
            echo '';
          }else{
            if($row[6] === 'Individual'){
              if ($row[1] == $row[2]) {
                $Cname = $row[1].'_'.$row[3];
              }else {
                $Cname = $row[1].'_'.$row[2].'_'.$row[3];
              }
            }else{
               $Cname = $row[5];
            }
            }
        sqlsrv_free_stmt( $stmt);

        $sql2 = "SELECT 'Loan Account' AS AccountType,
                         AccountNumber,
                         AccountName
                FROM loanLedger WHERE (ClientID = $cid) and (AccountNumber = $accno) UNION ALL
                SELECT 'Savings Deposit' AS AccountType,AccountNumber,AccountName
                FROM casaLedger WHERE (ClientID = $cid) and (AccountNumber = $accno) UNION ALL
                SELECT 'Time Deposit' AS AccountType,TDAccountNumber,AccountName
                FROM tdLedger WHERE (ClientId = $cid) and (tdAccountNumber = $accno);";
                $stmt2 = sqlsrv_query( $conn, $sql2);
                if( $stmt2 === false) {
                die( print_r( sqlsrv_errors(), true) );
            }
        $row2 = sqlsrv_fetch_array($stmt2);
          if($row2 === null){
            echo '';
          }else{
            $doctype = $row2[0];
            }
        sqlsrv_free_stmt( $stmt2);

        $sql1 = "SELECT Count(*) as docNo from ddmsFileName Where ClientID=$cid";
        $stmt1 = sqlsrv_query( $conn, $sql1);
            if( $stmt1 === false) {
            die( print_r( sqlsrv_errors(), true) );
        }
        $row1 = sqlsrv_fetch_array($stmt1);

        sqlsrv_free_stmt( $stmt1);

            $docNo1 = $row1['0'] + 1;
            $docNo = sprintf('%07d', $docNo1);


          if ($_FILES["mydoc"]["name"] != '')
          {
            $allowed_ext = array("jpg","pdf","png");
            $tmp = explode('.',$_FILES["mydoc"]["name"]);
            $ext = end($tmp);
            if (in_array($ext, $allowed_ext))
             {
              if (!file_exists('C:/xampp/htdocs/DDMS/DDMS/' .$Cname.'_'.$cid.'/'.$accno)) {
                  mkdir('C:/xampp/htdocs/DDMS/DDMS/' .$Cname.'_'.$cid.'/'.$accno, 0777, true);
                }
              $name = $Cname.'_'.$scanType.'_'.$cid.'_'.$docNo.'.'.$ext;
              $path = 'C:/xampp/htdocs/DDMS/DDMS/' .$Cname.'_'.$cid.'/'.$accno.'/';
              $docpath = 'DDMS/'.$Cname.'_'.$cid.'/'.$accno;

              if(move_uploaded_file($_FILES["mydoc"]["tmp_name"],$path.$name))
              {
                $sql2 ="INSERT INTO ddmsFileName(ClientID,AccountNumber,DocumentName,DocumentType,DocumentNo,DocumentPath,Uploaded_by,AccountType)VALUES(?,?,?,?,?,?,?,?)";
                $params = array($cid,$accno,$name,$scanType,$docNo,$docpath,$uid,$doctype);
                $stmt2 = sqlsrv_query( $conn, $sql2, $params);
                if( $stmt2 === false ) {
                die( print_r( sqlsrv_errors(), true));
                  }
                ?>
                <script>alert('FILE SUCCESSFULLY SAVED');
                    window.location.Refresh();
              </script>
                <?php
              }else{
                 ?>
                <script>alert('FILE DENIED')</script>
                <?php
              }

            }else{
              echo '<script>alert("INVALID FILE FORMAT")</script>';
          }
            }else{
            echo'<script>alert("PLEASE SELECT FILE")</script>';
          }
        }else{
            echo '<script>alert("PLEASE SELECT DOCUMENT TYPE")</script>';
        }
        }else{
          echo '<script>alert("FIND CLIENT ACCOUNT NUMBER")</script>';
        }
        }


       ?>
