#!/bin/bash

# this script should be run as root (e.g., with sudo) *after* get-dependencies.sh

# explicit error handling rather than set -e  (https://mywiki.wooledge.org/BashFAQ/105)

# make sure tools are installed
apt-get -y install extlinux git wget || exit 1

# docker build
docker build . -t textualization:ragged || exit 2

# export image
CID=$(docker run -d textualization:ragged /bin/true)
docker export -o ragged.tar ${CID} || exit 3
dd if=/dev/zero of=ragged.img bs=1G count=4 || exit 4
sfdisk ragged.img <<EOF || exit 4
label: dos
label-id: 0x4A663D
device: new.img
unit: sectors

ragged.img1 : start=2048, size=8386560, type=83, bootable
EOF
OFFSET=$(expr 512 \* 2048)
losetup -o ${OFFSET} /dev/loop0 ragged.img || exit 4
mkfs.ext3 /dev/loop0 || exit 5
mkdir -p ./mnt || exit 5
mount /dev/loop0 ./mnt || exit 5
tar -xf ragged.tar -C ./mnt/ || exit 6
rm ragged.tar || exit 6
extlinux --install ./mnt/boot/ || exit 7
cat > ./mnt/boot/syslinux.cfg  <<EOF || exit 7
DEFAULT linux
  SAY Booting The RAGged Edge Box...
  LABEL linux
  KERNEL /vmlinuz
  APPEND ro root=/dev/sda1 initrd=/initrd.img
EOF
umount ./mnt || exit 8
losetup -D || exit 8
dd if=/usr/lib/syslinux/mbr/mbr.bin of=ragged.img bs=440 count=1 conv=notrunc || exit 9

# export to virtualbox
#vboxmanage convertfromraw ragged.img "RAGged Edge Box.vdi" --format=VDI --uuid=15edf151-150e-4e81-9717-466a66208e06 || exit 10
vboxmanage convertfromraw ragged.img "RAGged Edge Box-disk001.vmdk" --format=VMDK --uuid=15edf151-150e-4e81-9717-466a66208e06 || exit 10
cp box/RAGged\ Edge\ Box.mf box/RAGged\ Edge\ Box.ovf .
echo -n "SHA1 (RAGged Edge Box-disk001.vmdk) = " >> RAGged\ Edge\ Box.mf
sha1sum "RAGged Edge Box-disk001.vmdk" | perl -pe 's/ .*//' >> RAGged\ Edge\ Box.mf
chmod 0660 "RAGged Edge Box-disk001.vmdk"
tar --owner=vboxovf10 --group=vbox_v7.0.18r162988 -cf RAGged_Edge_Box.ova RAGged\ Edge\ Box.ovf RAGged\ Edge\ Box-disk001.vmdk RAGged\ Edge\ Box.mf 

