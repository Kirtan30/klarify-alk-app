pipeline {
    agent any

    environment {
        battlbox_app_server_user = credentials('klarify_alk_app_server_user')
        battlbox_app_server_ip = credentials('klarify_alk_app_server_ip')
    }

    stages {
        stage('GIT Checkout') {
            steps {
                git credentialsId: 'github_secret_key', url: 'git@github.com:klarify-praella-team/klarify-alk-app.git'
            }
        }
        stage('Deploy battlbox-app') {
            steps {
                sshagent(['Remote ssh key']) {
                  sh 'cat deploy.sh | ssh $battlbox_app_server_user@$battlbox_app_server_ip -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null'
                }
            }
        }
    }
}
