# 初始化 GIT
git init
# 加载当前目录的所有文件
git add .
# 提交当前文件明细
git commit -m "init commit"
# 添加远程代码库
git remote rm origin
git remote add origin https://yourshell@github.com/yourshell/certs-backup.git
# 推送代码
git push -u origin master
