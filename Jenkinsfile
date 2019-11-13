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
        sh 'composer install --no-interaction --no-suggest'
      }
    }
    stage('Unit Testing') {
      steps {
          withCredentials([usernamePassword(credentialsId: 'ADYEN_CREDENTIALS', usernameVariable: 'ADYEN_USERNAME', passwordVariable: 'ADYEN_PASSWORD'),
          string(credentialsId: 'FACEBOOK_APP_SECRET', variable: 'FACEBOOK_APP_SECRET'),
          string(credentialsId: 'MC_API_KEY', variable: 'MC_API_KEY')]) {
            sh '''php -v
                php -m | grep "imagick"
                export DB_DATABASE="base_test_$(date +%Y%m%d%H%M%S)_$RANDOM"
                export MONGO_DATABASE="test_one-nosql_$(date +%Y%m%d%H%M%S)_$RANDOM"
                mysql -uroot -e "CREATE DATABASE $DB_DATABASE;"
                mysql -uroot -e "SET GLOBAL sql_mode = 'traditional';"
                php -d memory_limit=512M vendor/bin/phpunit
                mysql -uroot -e "DROP DATABASE IF EXISTS $DB_DATABASE;"
                mongo $MONGO_DATABASE --eval "db.dropDatabase()"'''
           }
      }
    }
  }
}

