/*#include <pcl/visualization/cloud_viewer.h>
 //...
 
 int main ()
 {
   pcl::PointCloud<pcl::PointXYZRGB>::Ptr cloud;
   //... populate cloud
   pcl::visualization::CloudViewer viewer ("Simple Cloud Viewer");
   viewer.showCloud (cloud);
   //while (!viewer.wasStopped ())
   //{
   //}
   return 0;
 }
*/

//#include <pcl/visualization/cloud_viewer.h>
#include <iostream>
#include <pcl/io/io.h>
#include <pcl/io/pcd_io.h>
#include <pcl/visualization/pcl_visualizer.h>
    
int user_data;
    
void pointPickingEventOccurred (const pcl::visualization::PointPickingEvent& event, void* viewer_void)
{
  std::cout << "[INOF] Point picking event occurred." << std::endl;

  float x, y, z;
  if (event.getPointIndex () == -1)
  {
     return;
  }
  event.getPoint(x, y, z);
  std::cout << "[INOF] Point coordinate ( " << x << ", " << y << ", " << z << ")" << std::endl;
}

void 
viewerOneOff (pcl::visualization::PCLVisualizer& viewer)
{
    viewer.setBackgroundColor (1.0, 0.5, 1.0);
    pcl::PointXYZ o;
    o.x = 1.0;
    o.y = 0;
    o.z = 0;
    //viewer.addSphere (o, 0.25, "sphere", 0);
    //std::cout << "i only run once" << std::endl;
    
}
    
void 
viewerPsycho (pcl::visualization::PCLVisualizer& viewer)
{
    static unsigned count = 0;
    std::stringstream ss;
    ss << "Once per viewer loop: " << count++;
    viewer.removeShape ("text", 0);
    viewer.addText (ss.str(), 200, 300, "text", 0);
    
    //FIXME: possible race condition here:
    user_data++;
}
    
int 
main ()
{
    pcl::PointCloud<pcl::PointXYZRGBA>::Ptr cloud (new pcl::PointCloud<pcl::PointXYZRGBA>);
    pcl::io::loadPCDFile ("trajektoria.pcd", *cloud);
    
    pcl::visualization::PCLVisualizer viewer("Cloud Viewer");
    viewer.setBackgroundColor (1.0, 0.5, 1.0);

    viewer.addPointCloud (cloud,"trajektoria");
    viewer.registerPointPickingCallback (pointPickingEventOccurred, (void*)&viewer);
    viewer.spin();
    
    
}