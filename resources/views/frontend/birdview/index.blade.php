@extends('layouts.bs')

@section('title', $title)

@section('css')
    <link rel="stylesheet" href="{{ asset('/') }}assets/frontend/css/bird_view.css">
@endsection

@section('content')
    <header>
        <div class="top_header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <div class="logo">
                            <img src="{{ asset('/') }}assets/frontend/image/bird_view.svg" alt="">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="shift_wrapper">
                            <h4><span id="shift_name">Shift D</span> <span id="shift_start_end_time">00:00 AM - 00:00 PM</span></h4>
                        </div>
                    </div>
                    <div class="col-md-3"></div>
                </div>
            </div>
        </div>
        <div class="header_bottom">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <div class="avarage_wrapper">
                            <ul>
                                <li>Avg. Efficiency <span class="text_orange" id="averageMachineEfficiency">00</span></li>
                                <li>Avg. Speed <span class="text_blue" id="averageMachineSpeed">00</span></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="rs_wrapper">
                            <ul>
                                <li class="r_box">R : <span id="totalMachineRunning">00</span></li>
                                <li class="s_box">S : <span id="totalMachineStop">00</span></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="percent_wrapper">
                            <ul>
                                <li class="bg_light_green"><span class="text_green" id="totalGreenEfficiency">00</span>90-100%</li>
                                <li class="bg_light_yellow"><span class="text_yellow" id="totalYellowEfficiency">00</span>70-90%</li>
                                <li class="bg_light_orange"><span class="text_orange" id="totalOrangeEfficiency">00</span>50-70%</li>
                                <li class="bg_light_red"><span class="text_red" id="totalRedEfficiency">00</span>50% & &lt;</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="machine_wrapper">
        <div class="container-fluid">
            <div class="machine_inner_wrapper" id="machine_data"></div>
        </div>
    </section>

    <div class="modal fade" id="machineModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="machine_detail_wrap">
                        <div id="machine_titleBackground" class="machine_title">
                            <h4><span id="machine_name"></span><span id="machine_dot" class="dot"></span></h4>
                        </div>
                        <div class="machine_speed_wrap">
                            <ul>
                                <li><span class="text_orange" id="machine_efficiency">00</span>Efficiency</li>
                                <li><span class="text_blue" id="machine_speed">00</span>Speed</li>
                            </ul>
                        </div>
                        <div class="machine_rs_wrap">
                            <ul>
                                <li class="text_green">R <span class="text_dark" id="machine_running">- 00h 00m</span></li>
                                <li class="text_red">S <span class="text_dark" id="machine_stop">- 00h 00m</span></li>
                            </ul>
                        </div>
                        <div class="machine_work_wrap">
                            <ul>
                                <li><span class="text_orange" id="machine_totalPickThisShift">00</span>Picks in this shift</li>
                                <li><span class="text_orange" id="machine_totalPickToday">00</span>Total Picks today</li>
                                <li><span class="text_red" id="machine_stoppages">00</span>Stoppages in this shift</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        let birdviewUrl = "{{ route('birdview.index') }}";
    </script>
    <script src="{{ asset('/') }}assets/frontend/js/bird_view.js"></script>
@endsection