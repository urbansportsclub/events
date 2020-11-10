pipeline {
  agent { node { label 'jenkins-agent' } }
  stages {
    stage('Stop old build') {
          steps {
              milestone label: '', ordinal:  Integer.parseInt(env.BUILD_ID) - 1
              milestone label: '', ordinal:  Integer.parseInt(env.BUILD_ID)
          }
    }
    stage('Composer') {
      steps {
        sh 'composer install --no-interaction --no-suggest -vvv'
      }
    }
    stage('Unit Testing') {
      steps {
          sh '''php -v
              php -m | grep -E "pcntl|rdkafka"
              php -d memory_limit=512M vendor/bin/phpunit'''
      }
    }
  }
}

