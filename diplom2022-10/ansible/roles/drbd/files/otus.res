resource otus {
 on drbdnode1 {
  device /dev/drbd0;
  disk /dev/sdb;
  meta-disk internal;
  address 10.0.0.30:7789;
 }
 on drbdnode2 {
  device /dev/drbd0;
  disk /dev/sdb;
  meta-disk internal;
  address 10.0.0.31:7789;
 }
}
