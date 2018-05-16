#! /bin/bash
#Copyright 2018 Simon Hrabos. All rights reserved.

#Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

#1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.

#2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

#THIS SOFTWARE IS PROVIDED BY THE FREEBSD PROJECT "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
#IN NO EVENT SHALL THE FREEBSD PROJECT OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR 
#SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT 
#OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

set -e
set +x

out_dir=~/Desktop/record_$(date +%Y-%m-%d_%H-%M-%S)
mkdir $out_dir

(

	source /opt/ros/kinetic/setup.bash
	source ~/catkin_ws/devel/setup.bash
	env
	mkdir $out_dir/ros-log
	export ROS_LOG_DIR=$out_dir/ros-log

	# imu -> imu_base:
	roslaunch sbg_driver sbg_ellipse.launch output_file:=$out_dir/imu_raw.json uart_port:=$(ls /dev/ttyUSB*) &
	pids="$! $pids"
	sleep 3s
	rosrun tf static_transform_publisher 0 0 0 0 0 3.14159 map imu_base 0.1 &
	pids="$! $pids"

	roslaunch velodyne_pointcloud VLP16_points.launch device_ip:=192.168.1.201 output_timestamps_file:=$out_dir/velodyne1.timestamps &
	pids="$! $pids"
	roslaunch velodyne_pointcloud VLP16_points_multi.launch device_ip:=192.168.1.202 frame_id:=velodyne2 port:=2369 output_timestamps_file:=$out_dir/velodyne2.timestamps &
	pids="$! $pids"

	rosrun tf static_transform_publisher 0 0 -1 1.57 0 1.57 imu velodyne_base 0.1 &
	pids="$! $pids"
	rosrun tf static_transform_publisher -0.107 0.016 0.05 0 1.57 0.7 velodyne_base velodyne1_base 0.1 &
	pids="$! $pids"
	#rosrun tf static_transform_publisher 0.107 0.016 0.054 0 -1.57 0.7 velodyne_base velodyne2_base 0.1 &
	rosrun tf static_transform_publisher 0.107 0.016 0.054 -2.5848573 -1.5565187 -3.0036214 velodyne_base velodyne2_base 0.1 &
	pids="$! $pids"
	rosrun tf static_transform_publisher 0 0 0 0 -1.57 1.57 velodyne1_base velodyne 0.1 &
	pids="$! $pids"
	rosrun tf static_transform_publisher 0 0 0 0 -1.57 1.57 velodyne2_base velodyne2 0.1 &
	pids="$! $pids"


	rosrun tf static_transform_publisher 0 0 0 0 0 3.14159 map imu_base 0.1 &
	pids="$! $pids"

	sleep 1s

	rviz -d ~/workspace/4recon/conf/4recon_collector.rviz &
	pids="$! $pids"

	pushd $out_dir
		result=$(~/workspace/4recon/data-collector/ros_recorder.py "/velodyne_points" "/velodyne2/velodyne_points" "/tf" $(rostopic list | grep imu | xargs) | grep '^#')
	popd

	kill $pids
	
	ptpcam -R 0x1018,0xFFFFFFFF
	lastId=`gphoto2 --list-files | tail -1 | awk '{print substr($1, 2)}' | head -n 1`
    gphoto2 --get-file $lastId --force-overwrite
    
	echo "***********result: $result*****************"

	if [ "$result" == "#canceled" ]
	then
		# aaaaaa 13 is bad luck ...
		rm -rf $out_dir
	fi

) |& tee $out_dir/collector-log.txt
