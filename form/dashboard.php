<?php
include("header.php");

$user_check = $_SESSION['login_user'];

$sql = "select id_p_role as role from t_account where username = '$user_check' ";
$result = mysqli_query($db,$sql);
$row = mysqli_fetch_array($result,MYSQLI_ASSOC);

$sql_pj = "select count(*) as JUM from t_peminjaman where create_date = curdate() ";
$result_pj = mysqli_query($db,$sql_pj);
$row_pj = mysqli_fetch_array($result_pj,MYSQLI_ASSOC);

$sql_ag = "select count(*) as JUM from t_anggota where create_date = curdate() ";
$result_ag = mysqli_query($db,$sql_ag);
$row_ag = mysqli_fetch_array($result_ag,MYSQLI_ASSOC);

$sql_bk = "select count(*) as JUM from t_buku where create_date = curdate() ";
$result_bk = mysqli_query($db,$sql_bk);
$row_bk = mysqli_fetch_array($result_bk,MYSQLI_ASSOC);

$sql_jb = "SELECT SUM(JUM) AS JUMLAH,ID FROM v_peminjaman WHERE username = '$user_check'";
$result_jb = mysqli_query($db,$sql_jb);
$row_jb = mysqli_fetch_array($result_jb,MYSQLI_ASSOC);

if ($row['role']==1 || $row['role']==2){
?>
<div id="page-wrapper">
	<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Dashboard</h1>
                </div>
                <!-- /.col-lg-12 -->
	</div>
	
	<div class="row">
		<div class="col-lg-3 col-md-6">
						<div class="panel panel-green">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-3">
									</div>
									<div class="col-xs-9 text-right">
										<div class="huge"><?php echo $row_pj['JUM'];?></div>
										<div>Peminjaman Hari ini</div>
									</div>
								</div>
							</div>
							<a href="data_peminjaman.php">
								<div class="panel-footer">
									<span class="pull-left">Lihat Detail</span>
									<span class="pull-right"></span>
									<div class="clearfix"></div>
								</div>
							</a>
						</div>
		</div>
		
		<div class="col-lg-3 col-md-6">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php echo $row_ag['JUM'];?></div>
                                    <div>Anggota Baru</div>
                                </div>
                            </div>
                        </div>
                        <a href="data_anggota.php">
                            <div class="panel-footer">
                                <span class="pull-left">Lihat Detail</span>
                                <span class="pull-right"></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
        </div>
		
		<div class="col-lg-3 col-md-6">
                    <div class="panel panel-yellow">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php echo $row_bk['JUM'];?></div>
                                    <div>Buku Baru</div>
                                </div>
                            </div>
                        </div>
                        <a href="data_buku.php">
                            <div class="panel-footer">
                                <span class="pull-left">Lihat Detail</span>
                                <span class="pull-right"></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
         </div>
	</div>
</div>
<?php
	}else{
?>
<div id="page-wrapper">
	<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Dashboard</h1>
                </div>
                <!-- /.col-lg-12 -->
	</div>
	
	<div class="row">
		<div class="col-lg-3 col-md-6">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php echo $row_jb['JUMLAH'];?></div>
                                    <div>Total Peminjaman</div>
                                </div>
                            </div>
                        </div>
                        <a href="history_peminjaman.php?id=<?php echo $row_jb['ID'];?>">
                            <div class="panel-footer">
                                <span class="pull-left">Lihat Detail</span>
                                <span class="pull-right"></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
        </div>
	</div>
</div>
<?php
	}
?>

<?php 
include "footer.php";

?>