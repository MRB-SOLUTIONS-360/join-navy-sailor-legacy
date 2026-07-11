<?php

/** @var yii\web\View $this */

$this->title = 'Join Navy Admin';
?>



<div class="row">
    <div class="col-12">   
    <h3 style="text-align: center; color: black;">Total Complete Application with Roll No : <span style="color: red;"><?= $total_generate_roll?></span></h3> 
        <!-- <div class="page-title-box">
            <div class="page-title-right">
                <form class="d-flex">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-light" id="dash-daterange">
                        <span class="input-group-text bg-primary border-primary text-white">
                            <i class="mdi mdi-calendar-range font-13"></i>
                        </span>
                    </div>
                    <a href="javascript: void(0);" class="btn btn-primary ms-2">
                        <i class="mdi mdi-autorenew"></i>
                    </a>
                </form>
            </div>
            <h4 class="page-title">Roll Generation Analytics Date Wise</h4>
        </div> -->
    </div>
</div>

<div class="row">
    <?php /* 
    <div class="col-xl-3 col-lg-4">
        <div class="card tilebox-one">
            <div class="card-body">
                <i class='uil uil-users-alt float-end'></i>
                <h6 class="text-uppercase mt-0">Total Applied</h6>
                <h2 class="my-2" id="active-users-count">121</h2>
                <p class="mb-0 text-muted">
                    <span class="text-success me-2"><span class="mdi mdi-arrow-up-bold"></span>
                        5.27%</span>
                    <span class="text-nowrap">Since last month</span>
                </p>
            </div> <!-- end card-body-->
        </div>        
       
        <!--end card-->
       
    </div> <!-- end col -->

    */ ?>

    <div class="col-xl-12 col-lg-12">
        <div class="card card-h-100">
            <div class="card-body">
            
                <?php

                if ($chart_data['have_value'] == 'yes') {

                ?>
                    <div dir="ltr">
                        <div id="sessions-overview" class="apex-charts mt-3" data-colors="#0acf97">
                        </div>
                    </div>
                <?php } ?>
            </div> <!-- end card-body-->


            <div class="card-body">
                <?php

                if ($sailorEligibility['have_value'] == 'yes') {

                ?>
                    <div dir="ltr">
                        <div id="sessions-overview_2" class="apex-charts mt-3" data-colors="#0acf97">
                        </div>
                    </div>
                <?php } ?>
            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div>
</div>

<script>
    $(document).ready(function() {
        var options = {
            series: [{
               // name: "Desktops",
                data: [<?= implode(',', $chart_data['value']) ?>]
            }],
            chart: {
                type: 'line',
                height: 350,
                zoom: {
            enabled: false
          }
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: false,
                }
            },
            dataLabels: {
                enabled: true
            },

            title: {
                text: 'No of Candidates Roll No Generating Date Wise',
                align: 'center'
            },
            grid: {
          row: {
            colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
            opacity: 0.5
          },
        },

            xaxis: {
                categories: [<?= implode(',', $chart_data['date']) ?>],
            }
        };

        var chart = new ApexCharts(document.querySelector("#sessions-overview"), options);
        chart.render();


        //////////  

        var options2 = {
            series: [{
               // name: "Desktops",
                data: [<?= implode(',', $sailorEligibility['value']) ?>]
            }],
            chart: {
                type: 'line',
                height: 350,
                zoom: {
            enabled: false
          }
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: false,
                }
            },
            dataLabels: {
                enabled: true
            },

            title: {
                text: 'Eligibility Check Date Wise',
                align: 'center'
            },
            grid: {
          row: {
            colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
            opacity: 0.5
          },
        },

            xaxis: {
                categories: [<?= implode(',', $sailorEligibility['date']) ?>],
            }
        };

        var chart = new ApexCharts(document.querySelector("#sessions-overview_2"), options2);
        chart.render();






    })
</script>