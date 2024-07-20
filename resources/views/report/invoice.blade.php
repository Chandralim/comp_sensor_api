<html>
    <head>
        <style>
            /** 
                Set the margins of the page to 0, so the footer and the header
                can be of the full height and width !
             **/
            /* @page {
                margin: 0cm 0cm;
            } */

            /** Define now the real margins of every page in the PDF **/
            /* body {
                margin-top: 3cm;
                margin-left: 2cm;
                margin-right: 2cm;
                margin-bottom: 2cm;
            } */

            /** Define the header rules **/
            /* header {
                position: fixed;
                top: 0cm;
                left: 2cm;
                right: 2cm;
                height: 3cm;

                border:solid 1px #ccc;
            } */

            /** Define the footer rules **/
            /* footer {
                position: fixed; 
                bottom: 0cm; 
                left: 0cm; 
                right: 0cm;
                height: 2cm;
            } */

            @page {
                margin: 130px 25px 250px 25px;
            }

            header {
                position: fixed;
                top: -120px;
                left: 0px;
                right: 0px;
                height: 114px;

                /** Extra personal styles **/
                /* background-color: #03a9f4;
                color: white;
                text-align: center; */
                /* line-height: 35px; */

                border:solid 1px #000;
            }

            footer {
                position: fixed; 
                bottom: -235px; 
                left: 0px; 
                right: 0px;
                height: 260px; 

                /** Extra personal styles **/
                 /* background-color: rgba(00,FF,00,0.5); */
                /*color: white;
                text-align: center;
                line-height: 35px; */
            }

            .bold{
                font-weight:bold;
            }

            .text-nowrap{
                /* text-wrap: nowrap; */
                white-space: nowrap;
            }

            table{
                width:100%; 
                border-collapse: separate; 
                border-spacing: 0;
            }

        </style>
    </head>
    <body>
        <!-- Define header and footer blocks before your content -->
        <header >
            <div style="float:left; width:1.98438cm; height:1.24354cm; padding:0.875cm 0.5cm 0.875cm 0.1cm; ">
                <img src="kim.png" width="100%" height="100%"/>
            </div>
            <div style="float:left; width:14cm; ">
                <div class="bold">PT. KAWASAN INDUSTRI MEDAN</div>
                <div class="bold">WISMA KAWASAN INDUSTRI MEDAN</div>
                <div>Jalan Pulau Batam No.1 Kompleks KIM Tahap II</div>
                <div>Saentis Percut Sei Tuan - Deli Serdang 20371</div>
                <div>Phone. (061) 6871177 Fax. (061) 6871088</div>
                <div>NPWP : 01.467.610.0-093.000 - 01.467.610.0.0-125.001</div>
            </div>
            <div class="bold" style="float:left;  padding-top:2.2cm; font-size:20px;">
                INVOICE
            </div>
            
        </header>

        <footer>
            <table>
                <tr>
                    <td style="width:50%; border:solid 1px #000;">
                        
                        <div style="width:100%; font-weight:bold; text-align:center;">
                            PENTING
                        </div>
                        <ol style="margin:0px; padding-left:20px;">
                            <li> Keterlambatan pembayaran angsuran cicilan , biaya pemeliharaan kawasan, biaya keamanan, biaya air bersih, biaya limbah, dan lain-lain dikenakan denda sebesar 2% (dua persen) untuk setiap bulan. </li>
                            <li> Keterlambatan pembayaran Pajak Pertambahan Nilai akan dikenakan denda sesuai ketentuan perpajakan yang berlaku. </li>
                            <li> Dalam bukti pembayaran, mohon dicantumkan Nomor Invoice (Faktur) yang dibayarkan. </li>
                        </ol>
                    </td>
                    <td class="bold" style="width:50%; border:solid 1px #000;">
                        Silahkan melakukan pembayaran sebelum tanggal kadaluarsa di atas dengan menggunakan nomor virtual account di bawah ini sebagai rekening tujuan transfer
                        <table style="font-size:18px;">
                            <tbody>
                                <tr>
                                    <td>Virtual Account BNI</td>
                                    <td>:</td>
                                    <td>9889888900000836</td>
                                </tr>
                                <tr>
                                    <td>Virtual Account Mandiri</td>
                                    <td>:</td>
                                    <td>8927588900000836</td>
                                </tr>
                            </tbody>
                        </table>
                        
                    </td>
                </tr>
            </table>

            <table>
                <tbody>
                    <tr>
                        <td>PIC Invoice</td>
                        <td>:</td>
                        <td> MR. JHS </td>
                    </tr>
                    <tr>
                        <td>Mobile Number</td>
                        <td>:</td>
                        <td> 08xxxxxxxxxx </td>
                    </tr>
                </tbody>
            </table>

            <table>
                <tbody>
                    <tr>
                        <td style="border:solid 1px #000;">Form No: INV/01-03</td>
                        <td style="border:solid 1px #000;">Rev No 00</td>
                        <td style="border:solid 1px #000;">Start Date : 01-01-2018</td>
                    </tr>
                </tbody>
            </table>

        </footer>

        <!-- Wrap the content of your PDF inside a main tag -->
        <main>
            <!-- <h1>Hello World</h1> -->
            <div>

                <table style="margin:10px 0px;">
                    <tbody>
                        <tr>
                            <td class="text-nowrap">To</td>
                            <td>:</td>
                            <td>{{$location_name}}</td>
                            <td style="width:10px;"></td>
                            <td class="text-nowrap">Invoice No</td>
                            <td>:</td>
                            <td>1044/KIM/SLI/06/23</td>
                        </tr>
                        <tr>
                            <td class="text-nowrap">NPWP</td>
                            <td>:</td>
                            <td>43.297.530.8.111.000</td>
                            <td style="width:10px;"></td>
                            <td class="text-nowrap">Invoice Date</td>
                            <td>:</td>
                            <td>27 Juni 2023</td>
                        </tr>
                        <tr>
                            <td class="text-nowrap">Address</td>
                            <td>:</td>
                            <td> JL. SUASA NO.1 SEI RENGAS II MEDAN AREA KOTA MEDAN </td>
                            <td style="width:10px;"></td>
                            <td class="text-nowrap">Due Date</td>
                            <td></td>
                            <td>27 Juli 2023</td>
                        </tr>

                    </tbody>
                </table>
                <table>
                    <thead>
                        <tr>
                            <th style="border:solid 1px #000;">Description</th>
                            <th style="border:solid 1px #000;">Quantity</th>
                            <th style="border:solid 1px #000;" colspan="2">Price</th>
                            <th style="border:solid 1px #000;" colspan="2">Amount (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($datas as $d)
                        <tr>
                            <td>
                                <div>
                                    {{$d['description']}} 
                                </div>
                                <small> 
                                    {{$d['from']}} - {{$d['to']}}
                                </small>
                            </td>
                            <td style="text-align:right;">{{$d['usage']}} m3</td>
                            <td>Rp</td>
                            <td style="text-align:right;"></td>
                            <td>Rp</td>
                            <td style="text-align:right;"></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="border-top:solid 1px #000; border-bottom:double 3px #000;">
                        <tr>
                            <td class="bold" colspan="4" style="text-align:right; padding-right:5px;"> Sub Total</td>
                            <td>Rp</td>
                            <td style="text-align:right;"></td>
                        </tr>
                        <tr>
                            <td class="bold" colspan="4" style="text-align:right; padding-right:5px;"> Add. Cost/Discount <sup style="color:red; margin-left:-2px;">*</sup></td>
                            <td>Rp</td>
                            <td style="text-align:right;"></td>
                        </tr>
                        <tr>
                            <td class="bold" colspan="4" style="text-align:right; padding-right:5px;"> TOTAL (Net)</td>
                            <td>Rp</td>
                            <td style="text-align:right;"></td>
                        </tr>
                    </tfoot>
                </table>

                <div style="width:100%; padding:10px 0px; border-bottom:solid 1px #000;">
                    <div style="width:100%; color:red;">
                        *) Additional Description  
                    </div>
                    <ol style="margin:0px; padding:0px 0px 0px 25px;">
                        <li>Additional </li>
                    </ol>
                </div>

                <table style="margin-top:10px;">
                    <tr>
                        <td style="width:50%; text-align:center;">Received By,</td>
                        <td style="width:50%; text-align:center;">PT. KAWASAN INDUSTRI MEDAN</td>
                    </tr>
                    <tr>
                        <td style="height:50px;">

                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:center;"></td>
                        <td style="text-align:center;"> (Miss M) </td>
                    </tr>
                    <tr>
                        <td style="text-align:center;"></td>
                        <td style="text-align:center;"> Manager </td>
                    </tr>
                </table>

            </div>
        </main>
    </body>
</html>