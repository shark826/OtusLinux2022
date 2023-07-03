resource otus {
 on node1 {
  device /dev/drbd0;
  disk /dev/sdb;
  meta-disk internal;
  address 192.168.56.150:7789;
 }
 on node2 {
  device /dev/drbd0;
  disk /dev/sdb;
  meta-disk internal;
  address 192.168.56.151:7789;
 }
}
