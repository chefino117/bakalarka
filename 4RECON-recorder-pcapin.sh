#! /bin/bash

set -e
set +x

if [ $# -ne "1" ]; then
	echo "ERROR, missing argument: input.pcap" >&2
	exit 1
fi

out_dir=~/Desktop/record_$(date +%Y-%m-%d_%H-%M-%S)
mkdir $out_dir

(

	source /opt/ros/kinetic/setup.bash
	source ~/catkin_ws/devel/setup.bash
	env
	mkdir $out_dir/ros-log
	export ROS_LOG_DIR=$out_dir/ros-log

	roslaunch velodyne_pointcloud VLP16_points.launch pcap:=$1 &
	pids="$! $pids"
	sleep 3s

        #TODO
        
    roslaunch /home/simon/usb_cam-test.launch &
    pids="$! $pids"
	#rosrun tf static_transform_publisher 0 0 0 0 0 3.14159 map velodyne 0.1 &
	rosrun tf static_transform_publisher 0 0 0 0 0 0 map velodyne 0.1 &
	pids="$! $pids"

	rviz -d ~/workspace/4recon/conf/4recon_collector.rviz &
	pids="$! $pids"

	pushd $out_dir
		result=$(~/workspace/4recon/data-collector/ros_recorder.py "/velodyne_points" "/velodyne2/velodyne_points" "/tf" "/usb_cam/image_raw"| grep '^#')
	popd

	kill $pids
	echo "***********result: $result*****************"

	if [ "$result" == "#canceled" ]
	then
		# aaaaaa 13 is bad luck ...
		rm -rf $out_dir
	fi

) |& tee $out_dir/collector-log.txt
